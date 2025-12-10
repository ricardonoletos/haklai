<?php
/**
 * Authentication Manager with 2FA
 * Haklai SaaS - Church Management System
 * 
 * Sistema de autenticação completo com:
 * - Login/Logout
 * - 2FA via email
 * - Remember me
 * - Password reset
 * - Account lockout
 * 
 * @author Jefter Ruthes
 * @version 1.0.0
 */

namespace Haklai\Core;

use Haklai\Models\User;

class Auth
{
    private static ?User $user = null;
    private static array $config;
    
    /**
     * Inicializa autenticação
     */
    public static function init(): void
    {
        Session::start();
        self::$config = require __DIR__ . '/../../config/app.php';
        
        // Carrega usuário da sessão
        if (Session::has('user_id')) {
            self::$user = User::find(Session::get('user_id'));
            
            // Valida se o usuário ainda está ativo
            if (self::$user && !self::$user->is_active) {
                self::logout();
            }
        }
    }
    
    /**
     * Tenta autenticar usuário
     * 
     * @param string $email
     * @param string $password
     * @param bool $remember
     * @return array ['success' => bool, 'message' => string, 'requires_2fa' => bool]
     */
    public static function attempt(string $email, string $password, bool $remember = false): array
    {
        $db = Database::getInstance();
        
        // Busca usuário por email
        $user = $db->queryOne(
            "SELECT * FROM users WHERE email = ? AND is_active = 1",
            [$email]
        );
        
        if (!$user) {
            self::logAuthAttempt($email, false, 'User not found');
            return [
                'success' => false,
                'message' => 'Email ou senha inválidos.',
                'requires_2fa' => false
            ];
        }
        
        // Verifica se conta está bloqueada
        if ($user->locked_until && strtotime($user->locked_until) > time()) {
            $minutesLeft = ceil((strtotime($user->locked_until) - time()) / 60);
            return [
                'success' => false,
                'message' => "Conta bloqueada. Tente novamente em {$minutesLeft} minutos.",
                'requires_2fa' => false
            ];
        }
        
        // Verifica senha
        if (!password_verify($password, $user->password_hash)) {
            self::handleFailedLogin($user->id);
            self::logAuthAttempt($email, false, 'Invalid password');
            return [
                'success' => false,
                'message' => 'Email ou senha inválidos.',
                'requires_2fa' => false
            ];
        }
        
        // Reseta contador de tentativas
        $db->update('users', [
            'login_attempts' => 0,
            'locked_until' => null
        ], 'id = :id', ['id' => $user->id]);
        
        // Verifica se 2FA está habilitado
        if ($user->two_factor_enabled || $user->role_level >= 3) {
            // Gera código 2FA
            $code = self::generate2FACode();
            $expires = date('Y-m-d H:i:s', time() + (self::$config['auth']['two_factor_code_lifetime'] * 60));
            
            $db->update('users', [
                'two_factor_code' => $code,
                'two_factor_expires' => $expires
            ], 'id = :id', ['id' => $user->id]);
            
            // Envia código por email
            self::send2FACode($user->email, $user->full_name, $code);
            
            // Armazena ID temporário na sessão
            Session::set('pending_2fa_user_id', $user->id);
            Session::set('remember_me', $remember);
            
            return [
                'success' => true,
                'message' => 'Código de verificação enviado para seu email.',
                'requires_2fa' => true
            ];
        }
        
        // Login sem 2FA
        self::loginUser($user, $remember);
        
        return [
            'success' => true,
            'message' => 'Login realizado com sucesso!',
            'requires_2fa' => false
        ];
    }
    
    /**
     * Verifica código 2FA
     */
    public static function verify2FA(string $code): array
    {
        $userId = Session::get('pending_2fa_user_id');
        
        if (!$userId) {
            return [
                'success' => false,
                'message' => 'Sessão expirada. Faça login novamente.'
            ];
        }
        
        $db = Database::getInstance();
        $user = $db->queryOne(
            "SELECT * FROM users WHERE id = ? AND is_active = 1",
            [$userId]
        );
        
        if (!$user) {
            Session::remove('pending_2fa_user_id');
            return [
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ];
        }
        
        // Verifica se código expirou
        if (!$user->two_factor_expires || strtotime($user->two_factor_expires) < time()) {
            Session::remove('pending_2fa_user_id');
            return [
                'success' => false,
                'message' => 'Código expirado. Faça login novamente.'
            ];
        }
        
        // Verifica código
        if ($user->two_factor_code !== $code) {
            return [
                'success' => false,
                'message' => 'Código inválido.'
            ];
        }
        
        // Limpa código 2FA
        $db->update('users', [
            'two_factor_code' => null,
            'two_factor_expires' => null
        ], 'id = :id', ['id' => $user->id]);
        
        // Faz login
        $remember = Session::get('remember_me', false);
        self::loginUser($user, $remember);
        
        Session::remove('pending_2fa_user_id');
        Session::remove('remember_me');
        
        return [
            'success' => true,
            'message' => 'Autenticação realizada com sucesso!'
        ];
    }
    
    /**
     * Efetua login do usuário
     */
    private static function loginUser(object $user, bool $remember = false): void
    {
        $db = Database::getInstance();
        
        // Regenera ID da sessão (segurança)
        Session::regenerate();
        
        // Armazena dados na sessão
        Session::set('user_id', $user->id);
        Session::set('tenant_id', $user->tenant_id);
        Session::set('role_level', $user->role_level);
        Session::set('logged_in_at', time());
        
        // Remember me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + (self::$config['session']['lifetime'] * 60));
            
            $db->update('users', [
                'remember_token' => $token,
                'session_expires_at' => $expires
            ], 'id = :id', ['id' => $user->id]);
            
            setcookie(
                'remember_token',
                $token,
                time() + (self::$config['session']['lifetime'] * 60),
                '/',
                '',
                false, // secure (true em HTTPS)
                true   // httponly
            );
        }
        
        // Atualiza último login
        $db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => Session::get('REMOTE_ADDR')
        ], 'id = :id', ['id' => $user->id]);
        
        // Carrega usuário
        self::$user = User::find($user->id);
        
        // Log
        self::logAuthAttempt($user->email, true, 'Login successful');
    }
    
    /**
     * Faz logout
     */
    public static function logout(): void
    {
        if (self::$user) {
            $db = Database::getInstance();
            
            // Remove remember token
            $db->update('users', [
                'remember_token' => null,
                'session_token' => null,
                'session_expires_at' => null
            ], 'id = :id', ['id' => self::$user->id]);
            
            // Log
            self::logAuthAttempt(self::$user->email, true, 'Logout');
        }
        
        // Remove cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Destroi sessão
        Session::destroy();
        self::$user = null;
    }
    
    /**
     * Verifica se usuário está autenticado
     */
    public static function check(): bool
    {
        self::init();
        return self::$user !== null;
    }
    
    /**
     * Obtém usuário autenticado
     */
    public static function user(): ?User
    {
        self::init();
        return self::$user;
    }
    
    /**
     * Obtém ID do usuário autenticado
     */
    public static function id(): ?int
    {
        self::init();
        return self::$user ? self::$user->id : null;
    }
    
    /**
     * Obtém tenant ID do usuário
     */
    public static function tenantId(): ?int
    {
        self::init();
        return self::$user ? self::$user->tenant_id : null;
    }
    
    /**
     * Verifica se usuário tem permissão
     */
    public static function hasRole(int $minLevel): bool
    {
        self::init();
        return self::$user && self::$user->role_level >= $minLevel;
    }
    
    /**
     * Gera código 2FA
     */
    private static function generate2FACode(): string
    {
        $length = self::$config['auth']['two_factor_code_length'];
        return str_pad((string)random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }
    
    /**
     * Envia código 2FA por email
     */
    private static function send2FACode(string $email, string $name, string $code): void
    {
        $subject = 'Código de Verificação - Haklai SaaS';
        
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #4A90E2;'>Código de Verificação</h2>
                <p>Olá, {$name}!</p>
                <p>Seu código de verificação é:</p>
                <div style='background: #f4f4f4; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
                    {$code}
                </div>
                <p><strong>Este código expira em " . self::$config['auth']['two_factor_code_lifetime'] . " minutos.</strong></p>
                <p>Se você não solicitou este código, ignore este email.</p>
                <hr style='margin: 30px 0;'>
                <p style='font-size: 12px; color: #666;'>Haklai SaaS - Sistema de Gestão de Igrejas</p>
            </div>
        </body>
        </html>
        ";
        
        // TODO: Implementar envio real via PHPMailer
        // Por enquanto, apenas log
        error_log("2FA Code for {$email}: {$code}");
    }
    
    /**
     * Trata falha de login
     */
    private static function handleFailedLogin(int $userId): void
    {
        $db = Database::getInstance();
        
        $user = $db->queryOne("SELECT login_attempts FROM users WHERE id = ?", [$userId]);
        $attempts = ($user->login_attempts ?? 0) + 1;
        
        $updateData = ['login_attempts' => $attempts];
        
        // Bloqueia conta após X tentativas
        if ($attempts >= self::$config['auth']['max_login_attempts']) {
            $lockDuration = self::$config['auth']['lockout_duration'] * 60; // minutos para segundos
            $updateData['locked_until'] = date('Y-m-d H:i:s', time() + $lockDuration);
        }
        
        $db->update('users', $updateData, 'id = :id', ['id' => $userId]);
    }
    
    /**
     * Registra tentativa de autenticação
     */
    private static function logAuthAttempt(string $email, bool $success, string $reason = ''): void
    {
        $db = Database::getInstance();
        
        try {
            $db->insert('audit_logs', [
                'user_id' => null,
                'action' => $success ? 'login_success' : 'login_failed',
                'description' => "Email: {$email} | Reason: {$reason}",
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Silenciosamente falha se não conseguir logar
        }
    }
}
