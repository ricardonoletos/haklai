<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haklai SaaS - Sistema de Gestão de Igrejas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #50C878;
            --dark-bg: #1a1a2e;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 100px 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .btn-hero {
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 50px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-3 fw-bold mb-4">Haklai SaaS</h1>
                    <p class="lead mb-4">
                        Sistema completo de gestão de igrejas com controle de células, 
                        presenças, relatórios e muito mais.
                    </p>
                    <div class="d-flex gap-3 mb-5">
                        <a href="/login" class="btn btn-light btn-hero">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Fazer Login
                        </a>
                        <a href="#features" class="btn btn-outline-light btn-hero">
                            Saiba Mais
                        </a>
                    </div>
                    <div class="d-flex gap-4 text-white-50">
                        <div>
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Multi-tenant
                        </div>
                        <div>
                            <i class="bi bi-shield-check me-2"></i>
                            Seguro
                        </div>
                        <div>
                            <i class="bi bi-phone me-2"></i>
                            Mobile-first
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="bi bi-building" style="font-size: 300px; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Funcionalidades</h2>
                <p class="lead text-muted">Tudo que você precisa em um só lugar</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h4>Gestão de Membros</h4>
                        <p class="text-muted">Cadastro completo com hierarquia, formações e histórico.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h4>Sistema de Células</h4>
                        <p class="text-muted">Controle de células com mapa interativo e reuniões.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <h4>Checkpoint</h4>
                        <p class="text-muted">Registro de presença otimizado para mobile.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h4>Relatórios</h4>
                        <p class="text-muted">Dashboards e relatórios com exportação PDF/Excel.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h4>Segurança 2FA</h4>
                        <p class="text-muted">Autenticação de dois fatores via email.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-phone-fill"></i>
                        </div>
                        <h4>Mobile App</h4>
                        <p class="text-muted">API REST completa para aplicativos mobile.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="py-4 bg-dark text-white text-center">
        <div class="container">
            <p class="mb-0">
                © 2025 Haklai SaaS - Desenvolvido por 
                <a href="https://ruthes.dev" target="_blank" class="text-white">Jefter Ruthes</a>
            </p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
