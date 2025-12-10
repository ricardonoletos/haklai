<?php
/**
 * Configurações da Aplicação
 * Haklai SaaS - Church Management System
 * Desenvolvido por: Jefter Ruthes
 */

return [
    // Informações da aplicação
    'name' => 'Haklai SaaS',
    'version' => '1.0.0',
    'env' => getenv('APP_ENV') ?: 'production', // development, production
    'debug' => getenv('APP_DEBUG') === 'true',
    'url' => getenv('APP_URL') ?: 'http://localhost',
    'timezone' => 'America/Sao_Paulo',
    'locale' => 'pt_BR',
    
    // Segurança
    'key' => getenv('APP_KEY') ?: 'base64:' . base64_encode(random_bytes(32)),
    'cipher' => 'AES-256-CBC',
    
    // Sessão
    'session' => [
        'lifetime' => 7 * 24 * 60, // 7 dias em minutos
        'cookie_name' => 'haklai_session',
        'cookie_path' => '/',
        'cookie_domain' => '',
        'cookie_secure' => false, // true em HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ],
    
    // Autenticação
    'auth' => [
        'password_min_length' => 8,
        'password_require_uppercase' => true,
        'password_require_numbers' => true,
        'password_require_symbols' => true,
        'max_login_attempts' => 5,
        'lockout_duration' => 30, // minutos
        'two_factor_enabled' => true,
        'two_factor_code_length' => 6,
        'two_factor_code_lifetime' => 10, // minutos
    ],
    
    // Upload
    'upload' => [
        'max_size' => 10 * 1024 * 1024, // 10MB
        'allowed_images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'allowed_documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'storage_path' => __DIR__ . '/../public/uploads/',
    ],
    
    // Email (para 2FA e notificações)
    'mail' => [
        'driver' => getenv('MAIL_DRIVER') ?: 'smtp',
        'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
        'port' => getenv('MAIL_PORT') ?: 587,
        'username' => getenv('MAIL_USERNAME') ?: '',
        'password' => getenv('MAIL_PASSWORD') ?: '',
        'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
        'from' => [
            'address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@haklai.com',
            'name' => getenv('MAIL_FROM_NAME') ?: 'Haklai SaaS',
        ],
    ],
    
    // Google Maps API
    'google_maps' => [
        'api_key' => getenv('GOOGLE_MAPS_API_KEY') ?: '',
    ],
    
    // Paginação
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100,
    ],
    
    // Logs
    'log' => [
        'enabled' => true,
        'path' => __DIR__ . '/../storage/logs/',
        'level' => 'info', // debug, info, warning, error
        'max_files' => 30, // dias
    ],
];
