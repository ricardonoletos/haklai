<?php
/**
 * Entry Point - Haklai SaaS
 * Church Management System
 * 
 * Desenvolvido por: Jefter Ruthes
 * https://ruthes.dev
 */

// Autoloader simples
spl_autoload_register(function ($class) {
    $prefix = 'Haklai\\';
    $base_dir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

use Haklai\Core\{Router, Session, Auth};

// Carrega configurações
$config = require __DIR__ . '/../config/app.php';

// Configurações PHP
error_reporting($config['debug'] ? E_ALL : 0);
ini_set('display_errors', $config['debug'] ? '1' : '0');
date_default_timezone_set($config['timezone']);

// Inicia sessão e autenticação
Session::start();
Auth::init();

// Cria router
$router = new Router();

// ============================================================
// ROTAS PÚBLICAS
// ============================================================

$router->get('/', function() {
    if (Auth::check()) {
        header('Location: /dashboard');
        exit;
    }
    require __DIR__ . '/../src/Views/index.php';
});

$router->get('/login', function() {
    if (Auth::check()) {
        header('Location: /dashboard');
        exit;
    }
    require __DIR__ . '/../src/Views/auth/login.php';
});

$router->post('/login', function() {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    $result = Auth::attempt($email, $password, $remember);
    
    if ($result['success'] && !$result['requires_2fa']) {
        header('Location: /dashboard');
        exit;
    } elseif ($result['requires_2fa']) {
        header('Location: /verify-2fa');
        exit;
    }
    
    Session::flash('error', $result['message']);
    header('Location: /login');
    exit;
});

$router->get('/verify-2fa', function() {
    if (!Session::has('pending_2fa_user_id')) {
        header('Location: /login');
        exit;
    }
    require __DIR__ . '/../src/Views/auth/verify-2fa.php';
});

$router->post('/verify-2fa', function() {
    $code = $_POST['code'] ?? '';
    $result = Auth::verify2FA($code);
    
    if ($result['success']) {
        header('Location: /dashboard');
        exit;
    }
    
    Session::flash('error', $result['message']);
    header('Location: /verify-2fa');
    exit;
});

$router->get('/logout', function() {
    Auth::logout();
    header('Location: /login');
    exit;
});

// ============================================================
// ROTAS PROTEGIDAS (Requerem autenticação)
// ============================================================

$authMiddleware = function() {
    if (!Auth::check()) {
        header('Location: /login');
        exit;
        return false;
    }
    return true;
};

$router->get('/dashboard', function() {
    require __DIR__ . '/../src/Views/dashboard/index.php';
}, [$authMiddleware]);

$router->get('/checkpoint', function() {
    if (!Auth::hasRole(1)) { // Apenas líderes ou superior
        Session::flash('error', 'Acesso negado.');
        header('Location: /dashboard');
        exit;
    }
    require __DIR__ . '/../src/Views/checkpoint/index.php';
}, [$authMiddleware]);

$router->get('/members', function() {
    require __DIR__ . '/../src/Views/members/index.php';
}, [$authMiddleware]);

$router->get('/cells', function() {
    require __DIR__ . '/../src/Views/cells/index.php';
}, [$authMiddleware]);

$router->get('/reports', function() {
    require __DIR__ . '/../src/Views/reports/index.php';
}, [$authMiddleware]);

// ============================================================
// API REST (para mobile app)
// ============================================================

$router->post('/api/auth/login', function() {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    $result = Auth::attempt($email, $password, false);
    
    if ($result['success'] && !$result['requires_2fa']) {
        $user = Auth::user();
        echo json_encode([
            'success' => true,
            'token' => Session::getId(),
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role_level' => $user->role_level,
                'tenant_id' => $user->tenant_id
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message'],
            'requires_2fa' => $result['requires_2fa'] ?? false
        ]);
    }
});

// ============================================================
// DESPACHA ROTA
// ============================================================

$router->dispatch();
