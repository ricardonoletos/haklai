<?php
/**
 * Session Manager
 * Haklai SaaS - Church Management System
 * 
 * Gerencia sessões de usuário com segurança
 * Proteção contra session hijacking e fixation
 * 
 * @author Jefter Ruthes
 * @version 1.0.0
 */

namespace Haklai\Core;

class Session
{
    private static bool $started = false;
    private static array $config;
    
    /**
     * Inicia sessão com configurações seguras
     */
    public static function start(): void
    {
        if (self::$started) {
            return;
        }
        
        self::$config = require __DIR__ . '/../../config/app.php';
        
        // Configurações de segurança da sessão
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_samesite', self::$config['session']['cookie_samesite']);
        
        if (self::$config['session']['cookie_secure']) {
            ini_set('session.cookie_secure', 1);
        }
        
        // Define configurações personalizadas
        session_name(self::$config['session']['cookie_name']);
        session_set_cookie_params([
            'lifetime' => self::$config['session']['lifetime'] * 60,
            'path' => self::$config['session']['cookie_path'],
            'domain' => self::$config['session']['cookie_domain'],
            'secure' => self::$config['session']['cookie_secure'],
            'httponly' => self::$config['session']['cookie_httponly'],
            'samesite' => self::$config['session']['cookie_samesite'],
        ]);
        
        session_start();
        self::$started = true;
        
        // Regenera ID da sessão periodicamente (proteção contra fixation)
        if (!self::has('_session_regenerated')) {
            session_regenerate_id(true);
            self::set('_session_regenerated', time());
        } elseif (time() - self::get('_session_regenerated') > 300) { // 5 minutos
            session_regenerate_id(true);
            self::set('_session_regenerated', time());
        }
        
        // Valida fingerprint (proteção contra hijacking)
        self::validateFingerprint();
    }
    
    /**
     * Define um valor na sessão
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Obtém um valor da sessão
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Verifica se uma chave existe na sessão
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove um valor da sessão
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }
    
    /**
     * Limpa toda a sessão
     */
    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
    }
    
    /**
     * Destroi a sessão completamente
     */
    public static function destroy(): void
    {
        self::start();
        
        // Limpa variáveis
        $_SESSION = [];
        
        // Destroi cookie da sessão
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroi sessão
        session_destroy();
        self::$started = false;
    }
    
    /**
     * Define flash message (mensagem que aparece uma vez)
     */
    public static function flash(string $key, $value): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }
    
    /**
     * Obtém flash message e remove
     */
    public static function getFlash(string $key, $default = null)
    {
        self::start();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
    
    /**
     * Verifica se existe flash message
     */
    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['_flash'][$key]);
    }
    
    /**
     * Valida fingerprint da sessão (proteção contra hijacking)
     */
    private static function validateFingerprint(): void
    {
        $fingerprint = self::generateFingerprint();
        
        if (!self::has('_fingerprint')) {
            self::set('_fingerprint', $fingerprint);
        } elseif (self::get('_fingerprint') !== $fingerprint) {
            // Fingerprint não corresponde - possível hijacking
            self::destroy();
            throw new \Exception('Sessão inválida. Por favor, faça login novamente.');
        }
    }
    
    /**
     * Gera fingerprint único do cliente
     */
    private static function generateFingerprint(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipAddress = self::getClientIP();
        
        return hash('sha256', $userAgent . $ipAddress);
    }
    
    /**
     * Obtém IP real do cliente
     */
    private static function getClientIP(): string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP', // CloudFlare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $ip = $_SERVER[$key];
                
                // Pega o primeiro IP se houver múltiplos
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                
                $ip = trim($ip);
                
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return '0.0.0.0';
    }
    
    /**
     * Obtém ID da sessão atual
     */
    public static function getId(): string
    {
        self::start();
        return session_id();
    }
    
    /**
     * Regenera ID da sessão
     */
    public static function regenerate(bool $deleteOld = true): void
    {
        self::start();
        session_regenerate_id($deleteOld);
        self::set('_session_regenerated', time());
    }
}
