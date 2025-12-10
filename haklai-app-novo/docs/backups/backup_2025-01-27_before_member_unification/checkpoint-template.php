<?php
/**
 * Template do Checkpoint de Célula
 * Baseado exatamente no arquivo test-checkpoint-final.html
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Verifica se há erro
if (isset($error)) {
    echo '<div class="alert alert-danger">' . esc_html($error) . '</div>';
    return;
}
?>

<div class="checkpoint-container">
    <!-- Header -->
    <div class="checkpoint-header">
        <div class="header-content">
            <h1><?php _e('Checkpoint de Célula', 'haklai-app'); ?></h1>
            <div class="subtitle">
                <?php 
                printf(
                    __('Controle de Presença - %s', 'haklai-app'),
                    esc_html($cell_info['name'] ?? __('Célula', 'haklai-app'))
                ); 
                ?>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="checkpoint-content">
        <!-- Stats Dashboard -->
        <div class="stats-dashboard">
            <h3 class="section-title"><?php _e('Dashboard de Presença', 'haklai-app'); ?></h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon">👥</div>
                    <div class="number"><?php echo esc_html($stats['total_members']); ?></div>
                    <div class="label"><?php _e('Total de Membros', 'haklai-app'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="icon">🔄</div>
                    <div class="number"><?php echo esc_html($stats['frequent_visitors']); ?></div>
                    <div class="label"><?php _e('F.A', 'haklai-app'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="icon">📅</div>
                    <div class="number"><?php echo esc_html($stats['last_meeting_present']); ?></div>
                    <div class="label"><?php _e('Na Última Célula', 'haklai-app'); ?></div>
                </div>
                <div class="stat-card">
                    <div class="icon">📊</div>
                    <div class="number"><?php echo esc_html($stats['presence_rate']); ?>%</div>
                    <div class="label"><?php _e('Taxa de Presença', 'haklai-app'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Controls Panel -->
        <div class="controls-panel">
            <h3 class="section-title"><?php _e('Controles da Reunião', 'haklai-app'); ?></h3>
            <div class="controls-grid">
                <div class="control-group">
                    <label class="control-label"><?php _e('Data da Reunião', 'haklai-app'); ?></label>
                    <input type="date" class="form-control" id="meetingDate" value="<?php echo esc_attr($meeting['meeting_date'] ?? date('Y-m-d')); ?>">
                </div>
                <div class="control-group">
                    <label class="control-label"><?php _e('Anfitrião', 'haklai-app'); ?></label>
                    <div class="search-container">
                        <input type="text" class="form-control" id="anfitriaoSearch" placeholder="<?php _e('Buscar membro...', 'haklai-app'); ?>" value="<?php echo esc_attr($meeting['host'] ?? ''); ?>">
                        <div class="search-results" id="searchResults"></div>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label"><?php _e('Endereço da Célula', 'haklai-app'); ?></label>
                    <input type="text" class="form-control" id="cellAddress" value="<?php echo esc_attr($cell_info['address'] ?? ''); ?>">
                </div>
            </div>
        </div>
        
        <!-- Members Section -->
        <div class="members-section">
            <h3 class="section-title"><?php _e('Membros da Célula', 'haklai-app'); ?></h3>
            <div class="members-grid">
                <?php foreach ($members as $member): ?>
                    <?php 
                    $is_present = false;
                    foreach ($attendance as $att) {
                        if ($att['member_id'] == $member['id']) {
                            $is_present = $att['is_present'];
                            break;
                        }
                    }
                    $member_class = $is_present ? 'present' : 'absent';
                    $photo_url = $member['photo'] ?: 'https://via.placeholder.com/80x80/10b981/ffffff?text=' . substr($member['name'], 0, 2);
                    ?>
                    <div class="member-card <?php echo $member_class; ?>" 
                         data-member-id="<?php echo esc_attr($member['id']); ?>"
                         data-baptism-status="<?php echo esc_attr($member['baptism_status']); ?>"
                         onclick="togglePresence(this)">
                        <img src="<?php echo esc_url($photo_url); ?>" 
                             alt="<?php echo esc_attr($member['name']); ?>" 
                             class="member-photo">
                        <div class="member-name"><?php echo esc_html($member['name']); ?></div>
                        <div class="member-role">
                            <?php echo esc_html(haklai_get_role_name($member['role_level'])); ?>
                        </div>
                        <div class="status-badge status-<?php echo $is_present ? 'present' : 'absent'; ?>">
                            <span><?php echo $is_present ? __('Presente', 'haklai-app') : __('Ausente', 'haklai-app'); ?></span>
                        </div>
                        <button type="button" class="btn-edit-member" onclick="event.stopPropagation(); editMember(<?php echo esc_js($member['id']); ?>)" title="<?php _e('Editar Membro', 'haklai-app'); ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="text-center mt-4">
            <button class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#addVisitorModal">
                <i class="fas fa-user-plus"></i> <?php _e('Adicionar Visitante', 'haklai-app'); ?>
            </button>
            <button class="btn btn-secondary me-3" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="fas fa-user-plus"></i> <?php _e('Registrar Novo Membro', 'haklai-app'); ?>
            </button>
            <button class="btn btn-success" onclick="sendReport()">
                <i class="fas fa-paper-plane"></i> <?php _e('Enviar Relatório', 'haklai-app'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Add Visitor Modal -->
<div class="modal fade" id="addVisitorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php _e('Adicionar Visitante', 'haklai-app'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addVisitorForm">
                    <div class="form-group">
                        <label class="form-label"><?php _e('Foto', 'haklai-app'); ?></label>
                        <input type="file" class="form-control" name="photo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php _e('Nome', 'haklai-app'); ?></label>
                        <input type="text" class="form-control" name="name" placeholder="<?php _e('Nome completo', 'haklai-app'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php _e('Telefone', 'haklai-app'); ?></label>
                        <input type="tel" 
                               class="form-control phone-input" 
                               name="phone" 
                               id="visitorPhone"
                               placeholder="<?php _e('Ex: +351 912 345 678 ou (11) 99999-9999', 'haklai-app'); ?>"
                               data-phone-validation>
                        <small class="form-text text-muted phone-hint">
                            <?php _e('Aceita formatos nacionais e internacionais', 'haklai-app'); ?>
                        </small>
                        <div class="invalid-feedback phone-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Cancelar', 'haklai-app'); ?></button>
                <button type="button" class="btn btn-primary" onclick="addVisitor()"><?php _e('Adicionar Visitante', 'haklai-app'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php _e('Registrar Novo Membro', 'haklai-app'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addMemberForm">
                    <div class="form-group">
                        <label class="form-label"><?php _e('Foto', 'haklai-app'); ?></label>
                        <input type="file" class="form-control" name="photo" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php _e('Nome', 'haklai-app'); ?></label>
                        <input type="text" class="form-control" name="name" placeholder="<?php _e('Nome completo', 'haklai-app'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php _e('Telefone', 'haklai-app'); ?></label>
                        <input type="tel" 
                               class="form-control phone-input" 
                               name="phone" 
                               id="memberPhone"
                               placeholder="<?php _e('Ex: +351 912 345 678 ou (11) 99999-9999', 'haklai-app'); ?>"
                               data-phone-validation>
                        <small class="form-text text-muted phone-hint">
                            <?php _e('Aceita formatos nacionais e internacionais', 'haklai-app'); ?>
                        </small>
                        <div class="invalid-feedback phone-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php _e('E-mail', 'haklai-app'); ?></label>
                        <input type="email" class="form-control" name="email" placeholder="email@exemplo.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php _e('Habilidade', 'haklai-app'); ?></label>
                        <select class="form-control" name="skill">
                            <option value=""><?php _e('Selecione uma habilidade', 'haklai-app'); ?></option>
                            <option value="musica"><?php _e('Música', 'haklai-app'); ?></option>
                            <option value="foto"><?php _e('Foto', 'haklai-app'); ?></option>
                            <option value="filmagem"><?php _e('Filmagem', 'haklai-app'); ?></option>
                            <option value="teatro"><?php _e('Teatro', 'haklai-app'); ?></option>
                            <option value="gastronomia"><?php _e('Gastronomia', 'haklai-app'); ?></option>
                            <option value="tecnologia"><?php _e('Tecnologia', 'haklai-app'); ?></option>
                            <option value="recepcao"><?php _e('Recepção', 'haklai-app'); ?></option>
                            <option value="som"><?php _e('Som e Projeção', 'haklai-app'); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php _e('Líder Associado', 'haklai-app'); ?></label>
                        <select class="form-control" name="leader_id">
                            <option value=""><?php _e('Selecione um líder', 'haklai-app'); ?></option>
                            <?php foreach ($members as $member): ?>
                                <?php if ($member['role_level'] >= 1): ?>
                                    <option value="<?php echo esc_attr($member['id']); ?>">
                                        <?php echo esc_html($member['name']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            ⭐ <?php _e('Encontro com Deus', 'haklai-app'); ?>
                        </label>
                        <div class="custom-control custom-switch" style="padding-top: 10px;">
                            <input type="checkbox" 
                                   class="custom-control-input" 
                                   id="add_encontro_deus" 
                                   name="encontro_deus"
                                   value="sim">
                            <label class="custom-control-label" for="add_encontro_deus">
                                <?php _e('Participou do Encontro com Deus', 'haklai-app'); ?>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            🎓 <?php _e('Formações Concluídas', 'haklai-app'); ?>
                        </label>
                        <div class="formacoes-checkboxes">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="add_formacao_ctl" 
                                       name="formacoes[]" 
                                       value="ctl">
                                <label class="custom-control-label" for="add_formacao_ctl">
                                    <?php _e('CTL - Curso de Treinamento de Líderes', 'haklai-app'); ?>
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="add_formacao_cme" 
                                       name="formacoes[]" 
                                       value="cme">
                                <label class="custom-control-label" for="add_formacao_cme">
                                    <?php _e('CME - Curso de Maturidade Espiritual', 'haklai-app'); ?>
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="add_formacao_stp" 
                                       name="formacoes[]" 
                                       value="stp">
                                <label class="custom-control-label" for="add_formacao_stp">
                                    <?php _e('STP - Seminário Teológico Pastoral', 'haklai-app'); ?>
                                </label>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <?php _e('Marque as formações que o membro já concluiu', 'haklai-app'); ?>
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php _e('Status de Batismo', 'haklai-app'); ?></label>
                        <select class="form-control" name="baptism_status">
                            <option value="visitor"><?php _e('Visitante', 'haklai-app'); ?></option>
                            <option value="frequent_visitor"><?php _e('Frequentador Assíduo', 'haklai-app'); ?></option>
                            <option value="member"><?php _e('Membro Batizado', 'haklai-app'); ?></option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Cancelar', 'haklai-app'); ?></button>
                <button type="button" class="btn btn-primary" onclick="addMember()"><?php _e('Registrar Membro', 'haklai-app'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit"></i> <?php _e('Editar Membro', 'haklai-app'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editMemberForm">
                    <input type="hidden" name="member_id" id="edit_member_id">
                    
                    <!-- Informações Básicas -->
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-user"></i> <?php _e('Informações Básicas', 'haklai-app'); ?>
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="form-label"><?php _e('Nome Completo', 'haklai-app'); ?> *</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label"><?php _e('E-mail', 'haklai-app'); ?></label>
                            <input type="email" class="form-control" name="email" id="edit_email">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label"><?php _e('Telefone', 'haklai-app'); ?></label>
                            <input type="tel" 
                                   class="form-control phone-input" 
                                   name="phone" 
                                   id="edit_phone"
                                   placeholder="<?php _e('Ex: +351 912 345 678 ou (11) 99999-9999', 'haklai-app'); ?>"
                                   data-phone-validation>
                            <small class="form-text text-muted phone-hint">
                                <?php _e('Aceita formatos nacionais e internacionais', 'haklai-app'); ?>
                            </small>
                            <div class="invalid-feedback phone-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?php _e('Foto (URL)', 'haklai-app'); ?></label>
                        <input type="url" class="form-control" name="photo" id="edit_photo">
                        <small class="form-text text-muted">
                            <?php _e('Informe a URL completa da foto do membro', 'haklai-app'); ?>
                        </small>
                    </div>
                    
                    <!-- Informações da Célula -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">
                        <i class="fas fa-home"></i> <?php _e('Informações da Célula', 'haklai-app'); ?>
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label"><?php _e('Célula', 'haklai-app'); ?></label>
                            <input type="text" class="form-control" id="edit_cell_name" readonly disabled>
                            <small class="form-text text-muted">
                                <?php _e('A célula não pode ser alterada nesta tela', 'haklai-app'); ?>
                            </small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">
                                ⭐ <?php _e('Encontro com Deus', 'haklai-app'); ?>
                            </label>
                            <div class="custom-control custom-switch" style="padding-top: 10px;">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="edit_encontro_deus" 
                                       name="encontro_deus"
                                       value="sim">
                                <label class="custom-control-label" for="edit_encontro_deus">
                                    <?php _e('Participou do Encontro', 'haklai-app'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Nova linha para Formações (ocupa toda a largura) -->
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="form-label">
                                🎓 <?php _e('Formações Concluídas', 'haklai-app'); ?>
                            </label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="edit_formacao_ctl" 
                                               name="formacoes[]" 
                                               value="ctl">
                                        <label class="custom-control-label" for="edit_formacao_ctl">
                                            <?php _e('CTL - Treinamento de Líderes', 'haklai-app'); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="edit_formacao_cme" 
                                               name="formacoes[]" 
                                               value="cme">
                                        <label class="custom-control-label" for="edit_formacao_cme">
                                            <?php _e('CME - Maturidade Espiritual', 'haklai-app'); ?>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="edit_formacao_stp" 
                                               name="formacoes[]" 
                                               value="stp">
                                        <label class="custom-control-label" for="edit_formacao_stp">
                                            <?php _e('STP - Seminário Teológico', 'haklai-app'); ?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <?php _e('Marque as formações concluídas pelo membro', 'haklai-app'); ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label"><?php _e('Habilidade/Ministério', 'haklai-app'); ?></label>
                            <select class="form-control" name="skill" id="edit_skill">
                                <option value=""><?php _e('Selecione uma habilidade', 'haklai-app'); ?></option>
                                <option value="musica"><?php _e('Música', 'haklai-app'); ?></option>
                                <option value="foto"><?php _e('Foto', 'haklai-app'); ?></option>
                                <option value="filmagem"><?php _e('Filmagem', 'haklai-app'); ?></option>
                                <option value="teatro"><?php _e('Teatro', 'haklai-app'); ?></option>
                                <option value="gastronomia"><?php _e('Gastronomia', 'haklai-app'); ?></option>
                                <option value="tecnologia"><?php _e('Tecnologia', 'haklai-app'); ?></option>
                                <option value="recepcao"><?php _e('Recepção', 'haklai-app'); ?></option>
                                <option value="som"><?php _e('Som e Projeção', 'haklai-app'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Status de Batismo -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">
                        <i class="fas fa-water"></i> <?php _e('Status Espiritual', 'haklai-app'); ?>
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label"><?php _e('Status de Batismo', 'haklai-app'); ?></label>
                            <select class="form-control" name="baptism_status" id="edit_baptism_status">
                                <option value="visitor"><?php _e('🚶 Visitante', 'haklai-app'); ?></option>
                                <option value="frequent_visitor"><?php _e('🔄 Frequentador Assíduo', 'haklai-app'); ?></option>
                                <option value="member"><?php _e('✅ Membro Batizado', 'haklai-app'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label"><?php _e('Data do Batismo', 'haklai-app'); ?></label>
                            <input type="date" class="form-control" name="baptism_date" id="edit_baptism_date">
                            <small class="form-text text-muted">
                                <?php _e('Preencha se o status for "Membro Batizado"', 'haklai-app'); ?>
                            </small>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="form-label"><?php _e('Status do Membro', 'haklai-app'); ?></label>
                            <select class="form-control" name="status" id="edit_status">
                                <option value="active"><?php _e('✅ Ativo', 'haklai-app'); ?></option>
                                <option value="inactive"><?php _e('⏸️ Inativo', 'haklai-app'); ?></option>
                                <option value="transferred"><?php _e('🔄 Transferido', 'haklai-app'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Alerta para mudança de status -->
                    <div class="alert alert-info mt-3" id="baptism_change_alert" style="display: none;">
                        <i class="fas fa-info-circle"></i>
                        <strong><?php _e('Mudança de Status de Batismo', 'haklai-app'); ?></strong>
                        <p class="mb-0"><?php _e('Você está prestes a mudar o status de batismo deste membro. Esta ação será registrada no histórico.', 'haklai-app'); ?></p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> <?php _e('Cancelar', 'haklai-app'); ?>
                </button>
                <button type="button" class="btn btn-primary" onclick="saveMemberEdit()">
                    <i class="fas fa-save"></i> <?php _e('Salvar Alterações', 'haklai-app'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Dados para JavaScript
window.haklaiCheckpoint = {
    meetingId: <?php echo esc_js($meeting['id'] ?? 0); ?>,
    cellId: <?php echo esc_js($cell_id ?? 0); ?>,
    nonce: '<?php echo wp_create_nonce('haklai_nonce'); ?>',
    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
    members: <?php echo json_encode(array_map(function($member) {
        return array(
            'id' => $member['id'],
            'name' => $member['name'],
            'role_level' => $member['role_level']
        );
    }, $members)); ?>
};

// Funções JavaScript globais para compatibilidade com o HTML original
function togglePresence(card) {
    if (window.HaklaiApp && typeof window.HaklaiApp.togglePresence === 'function') {
        window.HaklaiApp.togglePresence(card);
    } else {
        // Fallback simples
        $(card).trigger('click');
    }
}

function addMember() {
    if (window.HaklaiApp && typeof window.HaklaiApp.addMember === 'function') {
        window.HaklaiApp.addMember();
    } else {
        $('#addMemberForm').trigger('submit');
    }
}

function addVisitor() {
    if (window.HaklaiApp && typeof window.HaklaiApp.addVisitor === 'function') {
        window.HaklaiApp.addVisitor();
    } else {
        $('#addVisitorForm').trigger('submit');
    }
}

function sendReport() {
    if (window.HaklaiApp && typeof window.HaklaiApp.sendReport === 'function') {
        window.HaklaiApp.sendReport();
    } else {
        $('.btn-success').trigger('click');
    }
}

function editMember(memberId) {
    if (window.HaklaiApp && typeof window.HaklaiApp.editMember === 'function') {
        window.HaklaiApp.editMember(memberId);
    } else {
        // Fallback: carrega dados do membro via AJAX
        $.ajax({
            url: window.haklaiCheckpoint.ajaxUrl,
            type: 'POST',
            data: {
                action: 'haklai_ajax_handler',
                action_type: 'get_member_data',
                data: { member_id: memberId },
                nonce: window.haklaiCheckpoint.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    populateEditForm(response.data);
                    $('#editMemberModal').modal('show');
                }
            }
        });
    }
}

function saveMemberEdit() {
    if (window.HaklaiApp && typeof window.HaklaiApp.saveMemberEdit === 'function') {
        window.HaklaiApp.saveMemberEdit();
    } else {
        $('#editMemberForm').trigger('submit');
    }
}

function populateEditForm(memberData) {
    $('#edit_member_id').val(memberData.id);
    $('#edit_name').val(memberData.name);
    $('#edit_email').val(memberData.email || '');
    $('#edit_phone').val(memberData.phone || '');
    $('#edit_photo').val(memberData.photo || '');
    $('#edit_cell_name').val(memberData.cell_name || '');
    $('#edit_skill').val(memberData.skill || '');
    $('#edit_baptism_status').val(memberData.baptism_status || 'visitor');
    $('#edit_baptism_date').val(memberData.baptism_date || '');
    $('#edit_status').val(memberData.status || 'active');
    
    // Encontro com Deus
    $('#edit_encontro_deus').prop('checked', memberData.encontro_deus === 'sim' || memberData.encontro_deus === '1');
    
    // Formações
    $('input[name="formacoes[]"]').prop('checked', false); // Limpa tudo primeiro
    if (memberData.formacoes && Array.isArray(memberData.formacoes)) {
        memberData.formacoes.forEach(function(formacao) {
            $(`input[name="formacoes[]"][value="${formacao}"]`).prop('checked', true);
        });
    }
    
    // Guarda status original para detectar mudanças
    $('#edit_baptism_status').data('original-value', memberData.baptism_status);
}

function selectAnfitriao(name) {
    if (window.HaklaiApp && typeof window.HaklaiApp.selectAnfitriao === 'function') {
        window.HaklaiApp.selectAnfitriao(name);
    } else {
        $('#anfitriaoSearch').val(name);
        $('#searchResults').hide();
    }
}

// Função de atualização de estatísticas (compatibilidade com HTML original)
function updateStats() {
    if (window.HaklaiApp && typeof window.HaklaiApp.updateStats === 'function') {
        window.HaklaiApp.updateStats();
    } else {
        // Fallback simples
        const $cards = document.querySelectorAll('.member-card');
        const totalMembers = $cards.length;
        const presentMembers = document.querySelectorAll('.member-card.present').length;
        
        // Conta Frequentadores Assíduos (F.A)
        let frequentVisitors = 0;
        $cards.forEach(card => {
            if (card.getAttribute('data-baptism-status') === 'frequent_visitor') {
                frequentVisitors++;
            }
        });
        
        // Na Última Célula = Presentes na reunião atual
        const lastMeetingPresent = presentMembers;
        
        const presenceRate = totalMembers > 0 ? Math.round((presentMembers / totalMembers) * 100 * 10) / 10 : 0;
        
        document.querySelector('.stat-card:nth-child(1) .number').textContent = totalMembers;
        document.querySelector('.stat-card:nth-child(2) .number').textContent = frequentVisitors;
        document.querySelector('.stat-card:nth-child(3) .number').textContent = lastMeetingPresent;
        document.querySelector('.stat-card:nth-child(4) .number').textContent = presenceRate + '%';
    }
}

/**
 * Validação de Telefone Internacional e Nacional
 */
function validatePhoneNumber(phoneInput) {
    const phone = phoneInput.value.trim();
    const $input = $(phoneInput);
    const $feedback = $input.siblings('.phone-feedback');
    
    // Se campo vazio, não valida (não é obrigatório)
    if (phone === '') {
        $input.removeClass('is-invalid is-valid');
        $feedback.text('');
        return true;
    }
    
    // Remove espaços para contar dígitos
    const digitsOnly = phone.replace(/\D/g, '');
    
    // Validações
    const validations = {
        // Mínimo de 8 dígitos (números locais curtos)
        minLength: digitsOnly.length >= 8,
        
        // Máximo de 15 dígitos (padrão E.164)
        maxLength: digitsOnly.length <= 15,
        
        // Caracteres permitidos: números, espaços, +, -, (, )
        validChars: /^[\d\s\+\-\(\)]+$/.test(phone),
        
        // Se começar com +, deve ter código do país válido
        validInternational: phone.startsWith('+') ? /^\+\d{1,3}[\s\-]?/.test(phone) : true
    };
    
    // Mensagens de erro específicas
    const errors = [];
    if (!validations.validChars) {
        errors.push('<?php _e('Apenas números e caracteres (+, -, espaço, parênteses) são permitidos', 'haklai-app'); ?>');
    }
    if (!validations.minLength) {
        errors.push('<?php _e('Número muito curto (mínimo 8 dígitos)', 'haklai-app'); ?>');
    }
    if (!validations.maxLength) {
        errors.push('<?php _e('Número muito longo (máximo 15 dígitos)', 'haklai-app'); ?>');
    }
    if (!validations.validInternational) {
        errors.push('<?php _e('Formato internacional inválido', 'haklai-app'); ?>');
    }
    
    // Verifica se passou em todas as validações
    const isValid = Object.values(validations).every(v => v === true);
    
    if (isValid) {
        $input.removeClass('is-invalid').addClass('is-valid');
        $feedback.text('✓ <?php _e('Número válido', 'haklai-app'); ?>').removeClass('text-danger').addClass('text-success');
        return true;
    } else {
        $input.removeClass('is-valid').addClass('is-invalid');
        $feedback.text(errors.join('. ')).removeClass('text-success').addClass('text-danger');
        return false;
    }
}

/**
 * Formata telefone em tempo real (opcional - apenas visual)
 */
function formatPhoneAsYouType(phoneInput) {
    const phone = phoneInput.value;
    const cursorPos = phoneInput.selectionStart;
    
    // Se começar com +, não formata automaticamente (internacional)
    if (phone.startsWith('+')) {
        return;
    }
    
    // Detecta padrão brasileiro e formata
    const digitsOnly = phone.replace(/\D/g, '');
    
    if (digitsOnly.length === 11) {
        // Celular brasileiro: (XX) XXXXX-XXXX
        const formatted = digitsOnly.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
        if (formatted !== phone) {
            phoneInput.value = formatted;
            // Ajusta cursor
            const adjustment = formatted.length - phone.length;
            phoneInput.setSelectionRange(cursorPos + adjustment, cursorPos + adjustment);
        }
    } else if (digitsOnly.length === 10) {
        // Fixo brasileiro: (XX) XXXX-XXXX
        const formatted = digitsOnly.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
        if (formatted !== phone) {
            phoneInput.value = formatted;
            const adjustment = formatted.length - phone.length;
            phoneInput.setSelectionRange(cursorPos + adjustment, cursorPos + adjustment);
        }
    }
}

// Inicialização quando o documento estiver pronto
jQuery(document).ready(function($) {
    // Detecta mudança no status de batismo para mostrar alerta
    $('#edit_baptism_status').on('change', function() {
        const originalValue = $(this).data('original-value');
        const newValue = $(this).val();
        
        if (originalValue && originalValue !== newValue) {
            $('#baptism_change_alert').slideDown();
        } else {
            $('#baptism_change_alert').slideUp();
        }
        
        // Mostra/esconde data de batismo
        if (newValue === 'member') {
            $('#edit_baptism_date').closest('.form-group').show();
        }
    });
    
    // Validação de telefone em tempo real
    $('[data-phone-validation]').on('input', function() {
        validatePhoneNumber(this);
    });
    
    // Validação ao sair do campo
    $('[data-phone-validation]').on('blur', function() {
        validatePhoneNumber(this);
    });
    
    // Formatação automática (opcional)
    $('[data-phone-validation]').on('input', function() {
        // Descomente a linha abaixo se quiser formatação automática para números brasileiros
        // formatPhoneAsYouType(this);
    });
    
    // Validação antes de enviar formulários
    $('#addVisitorForm, #addMemberForm').on('submit', function(e) {
        const $phoneInputs = $(this).find('[data-phone-validation]');
        let allValid = true;
        
        $phoneInputs.each(function() {
            if (!validatePhoneNumber(this)) {
                allValid = false;
            }
        });
        
        if (!allValid) {
            e.preventDefault();
            alert('<?php _e('Por favor, corrija os erros no formulário antes de continuar.', 'haklai-app'); ?>');
            return false;
        }
    });
    
    // Busca de anfitrião
    $('#anfitriaoSearch').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const $results = $('#searchResults');
        
        if (searchTerm.length > 2) {
            // Filtra membros disponíveis
            const availableMembers = window.haklaiCheckpoint.members.filter(member => 
                member.name.toLowerCase().includes(searchTerm)
            );
            
            if (availableMembers.length > 0) {
                let html = '';
                availableMembers.forEach(member => {
                    html += `<div class="search-result-item" onclick="selectAnfitriao('${member.name}')">${member.name}</div>`;
                });
                $results.html(html).show();
            } else {
                $results.hide();
            }
        } else {
            $results.hide();
        }
    });
    
    // Fecha resultados de busca ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-container').length) {
            $('#searchResults').hide();
        }
    });
    
    // Atualiza estatísticas iniciais
    updateStats();
});
</script>

