<?php
use Haklai\Core\{Auth, Database};

$user = Auth::user();
$db = Database::getInstance();

// Busca células do usuário (se for líder)
$cells = [];
if (Auth::hasRole(1)) {
    $cells = $db->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM members m WHERE m.cell_id = c.id AND m.status = 'active') as member_count
        FROM cells c
        WHERE c.tenant_id = ? AND c.status = 'active'
        AND (c.leader_id = ? OR ? >= 99)
        ORDER BY c.name
    ", [Auth::tenantId(), $user->member_id, $user->role_level]);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkpoint - Haklai SaaS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .member-card {
            transition: all 0.3s;
            cursor: pointer;
        }
        .member-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .member-card.present {
            border-left: 5px solid #50C878;
        }
        .member-card.absent {
            border-left: 5px solid #E74C3C;
        }
        .btn-presence {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="bi bi-arrow-left me-2"></i>
                Checkpoint
            </a>
            <span class="text-white">
                <?= htmlspecialchars($user->full_name) ?>
            </span>
        </div>
    </nav>
    
    <div class="container py-4">
        <?php if (empty($cells)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Você não tem células associadas. Entre em contato com o administrador.
            </div>
        <?php else: ?>
            <!-- Seleção de Célula -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">1. Selecione a Célula</h5>
                    <select class="form-select form-select-lg" id="cellSelect">
                        <option value="">-- Escolha uma célula --</option>
                        <?php foreach ($cells as $cell): ?>
                            <option value="<?= $cell->id ?>" data-members="<?= $cell->member_count ?>">
                                <?= htmlspecialchars($cell->name) ?> (<?= $cell->member_count ?> membros)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Informações da Reunião -->
            <div class="card mb-4" id="meetingInfoCard" style="display: none;">
                <div class="card-body">
                    <h5 class="card-title">2. Informações da Reunião</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Data</label>
                            <input type="date" class="form-control" id="meetingDate" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Horário</label>
                            <input type="time" class="form-control" id="meetingTime" 
                                   value="<?= date('H:i') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Anfitrião</label>
                            <input type="text" class="form-control" id="hostName" 
                                   placeholder="Nome do anfitrião" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Local da Reunião</label>
                            <textarea class="form-control" id="meetingAddress" rows="2" 
                                      placeholder="Endereço completo" required></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lista de Presença -->
            <div id="attendanceContainer" style="display: none;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>3. Marcar Presenças</h5>
                    <div>
                        <span class="badge bg-success me-2">
                            <i class="bi bi-check-circle"></i>
                            <span id="presentCount">0</span> presentes
                        </span>
                        <span class="badge bg-danger">
                            <i class="bi bi-x-circle"></i>
                            <span id="absentCount">0</span> ausentes
                        </span>
                    </div>
                </div>
                
                <div id="membersList" class="row g-3">
                    <!-- Membros serão carregados via JavaScript -->
                </div>
                
                <div class="card mt-4">
                    <div class="card-body">
                        <button type="button" class="btn btn-primary btn-presence" id="btnSaveAttendance">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Salvar Presença
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sistema de Checkpoint - JavaScript básico
        let selectedCell = null;
        let members = [];
        let attendance = {};
        
        document.getElementById('cellSelect')?.addEventListener('change', function() {
            const cellId = this.value;
            if (cellId) {
                selectedCell = cellId;
                document.getElementById('meetingInfoCard').style.display = 'block';
                loadMembers(cellId);
            }
        });
        
        function loadMembers(cellId) {
            // Em produção, fazer fetch para API
            // Por enquanto, mockup simples
            document.getElementById('attendanceContainer').style.display = 'block';
            
            // Aqui viria a chamada AJAX para carregar membros
            // fetch(`/api/cells/${cellId}/members`)
        }
        
        function togglePresence(memberId) {
            attendance[memberId] = !attendance[memberId];
            updateCounts();
            updateMemberCard(memberId);
        }
        
        function updateCounts() {
            const present = Object.values(attendance).filter(v => v).length;
            const absent = Object.keys(attendance).length - present;
            
            document.getElementById('presentCount').textContent = present;
            document.getElementById('absentCount').textContent = absent;
        }
        
        function updateMemberCard(memberId) {
            const card = document.getElementById(`member-${memberId}`);
            if (attendance[memberId]) {
                card.classList.add('present');
                card.classList.remove('absent');
            } else {
                card.classList.add('absent');
                card.classList.remove('present');
            }
        }
        
        document.getElementById('btnSaveAttendance')?.addEventListener('click', function() {
            if (confirm('Confirma o salvamento das presenças?')) {
                // Salvar via API
                alert('Presenças salvas com sucesso!');
                window.location.href = '/dashboard';
            }
        });
    </script>
</body>
</html>
