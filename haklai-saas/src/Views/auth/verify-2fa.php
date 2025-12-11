<?php use Haklai\Core\Session; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação 2FA - Haklai SaaS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #4A90E2, #50C878); min-height: 100vh; display: flex; align-items: center; }
        .code-input { font-size: 32px; letter-spacing: 10px; text-align: center; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-lg border-0" style="max-width: 450px; margin: 0 auto;">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock" style="font-size: 48px; color: #4A90E2;"></i>
                    <h2 class="fw-bold mt-3">Verificação 2FA</h2>
                    <p class="text-muted">Digite o código enviado para seu email</p>
                </div>
                
                <?php if (Session::hasFlash('error')): ?>
                    <div class="alert alert-danger"><?= Session::getFlash('error') ?></div>
                <?php endif; ?>
                
                <form method="POST" action="/verify-2fa">
                    <div class="mb-4">
                        <input type="text" name="code" class="form-control code-input" 
                               placeholder="000000" maxlength="6" required autofocus 
                               pattern="[0-9]{6}">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="bi bi-check-circle me-2"></i>
                        Verificar Código
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        O código expira em 10 minutos
                    </small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
