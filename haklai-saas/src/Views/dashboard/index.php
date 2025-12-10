<?php
use Haklai\Core\{Auth, Database};

$user = Auth::user();
$db = Database::getInstance();

// Estatísticas básicas
$stats = [
    'total_members' => $db->queryOne("SELECT COUNT(*) as count FROM members WHERE tenant_id = ? AND status = 'active'", [Auth::tenantId()])->count ?? 0,
    'total_cells' => $db->queryOne("SELECT COUNT(*) as count FROM cells WHERE tenant_id = ? AND status = 'active'", [Auth::tenantId()])->count ?? 0,
    'total_meetings' => $db->queryOne("SELECT COUNT(*) as count FROM meetings WHERE tenant_id = ? AND meeting_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)", [Auth::tenantId()])->count ?? 0,
    'avg_attendance' => $db->queryOne("SELECT AVG(attendance_rate) as avg FROM meetings WHERE tenant_id = ? AND status = 'completed'", [Auth::tenantId()])->avg ?? 0
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Haklai SaaS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar { min-height: 100vh; background: #1a1a2e; color: white; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 12px 20px; display: block; }
        .sidebar a:hover { background: rgba(255,255,255,0.1); color: white; }
        .stat-card { border-left: 4px solid #4A90E2; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4 class="fw-bold">
                        <i class="bi bi-building me-2"></i>
                        Haklai
                    </h4>
                </div>
                
                <nav>
                    <a href="/dashboard" class="active">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                    <?php if (Auth::hasRole(1)): ?>
                    <a href="/checkpoint">
                        <i class="bi bi-check-circle me-2"></i>
                        Checkpoint
                    </a>
                    <?php endif; ?>
                    <a href="/members">
                        <i class="bi bi-people me-2"></i>
                        Membros
                    </a>
                    <a href="/cells">
                        <i class="bi bi-geo-alt me-2"></i>
                        Células
                    </a>
                    <a href="/reports">
                        <i class="bi bi-graph-up me-2"></i>
                        Relatórios
                    </a>
                </nav>
                
                <div class="position-absolute bottom-0 w-100 p-3">
                    <div class="text-white-50 small">
                        <div class="mb-2"><?= htmlspecialchars($user->full_name) ?></div>
                        <div class="mb-2"><?= $user->getRoleName() ?></div>
                        <a href="/logout" class="text-danger">
                            <i class="bi bi-box-arrow-right me-1"></i>
                            Sair
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Dashboard</h2>
                    <span class="badge bg-primary">Plano: Free</span>
                </div>
                
                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small">Total de Membros</div>
                                        <h3 class="fw-bold"><?= number_format($stats['total_members']) ?></h3>
                                    </div>
                                    <i class="bi bi-people" style="font-size: 40px; color: #4A90E2;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small">Células Ativas</div>
                                        <h3 class="fw-bold"><?= number_format($stats['total_cells']) ?></h3>
                                    </div>
                                    <i class="bi bi-geo-alt" style="font-size: 40px; color: #50C878;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small">Reuniões (30 dias)</div>
                                        <h3 class="fw-bold"><?= number_format($stats['total_meetings']) ?></h3>
                                    </div>
                                    <i class="bi bi-calendar-check" style="font-size: 40px; color: #FFD700;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small">Taxa de Presença</div>
                                        <h3 class="fw-bold"><?= number_format($stats['avg_attendance'], 1) ?>%</h3>
                                    </div>
                                    <i class="bi bi-graph-up" style="font-size: 40px; color: #E74C3C;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Ações Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php if (Auth::hasRole(1)): ?>
                            <div class="col-md-4">
                                <a href="/checkpoint" class="btn btn-outline-primary w-100 py-3">
                                    <i class="bi bi-check-circle d-block mb-2" style="font-size: 32px;"></i>
                                    Registrar Presença
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-md-4">
                                <a href="/members" class="btn btn-outline-success w-100 py-3">
                                    <i class="bi bi-person-plus d-block mb-2" style="font-size: 32px;"></i>
                                    Adicionar Membro
                                </a>
                            </div>
                            
                            <div class="col-md-4">
                                <a href="/reports" class="btn btn-outline-info w-100 py-3">
                                    <i class="bi bi-file-earmark-bar-graph d-block mb-2" style="font-size: 32px;"></i>
                                    Ver Relatórios
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
