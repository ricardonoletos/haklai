<?php
/**
 * Template da Aba de Sincronização
 * Gerencia sincronização CPT ↔ Tabelas MySQL
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

// Obtém status de sincronização
$sync_status = class_exists('Haklai_Sync_Manager') ? Haklai_Sync_Manager::get_sync_status() : null;
?>

<div class="haklai-sync-tab">
    <div class="tab-header">
        <h2>
            <i class="fas fa-sync-alt"></i>
            <?php _e('Sincronização CPT ↔ Tabelas MySQL', 'haklai-app'); ?>
        </h2>
        <p class="tab-description">
            <?php _e('Gerencie a sincronização entre Custom Post Types (CPTs) e tabelas MySQL otimizadas. Valide integridade, repare mapeamentos e monitore o status de sincronização.', 'haklai-app'); ?>
        </p>
    </div>
    
    <!-- Status Geral -->
    <div class="sync-status-section" style="background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0;">
            <i class="fas fa-chart-line"></i>
            <?php _e('Status Geral de Sincronização', 'haklai-app'); ?>
        </h3>
        
        <?php if ($sync_status): ?>
            <div class="status-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                <div class="status-card" style="background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #0073aa;">
                    <div style="font-size: 24px; font-weight: bold; color: #0073aa;">
                        <?php echo esc_html($sync_status['total_cpts']); ?>
                    </div>
                    <div style="color: #666; font-size: 14px;">
                        <?php _e('Total de CPTs', 'haklai-app'); ?>
                    </div>
                </div>
                
                <div class="status-card" style="background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;">
                    <div style="font-size: 24px; font-weight: bold; color: #28a745;">
                        <?php echo esc_html($sync_status['mapped_cpts']); ?>
                    </div>
                    <div style="color: #666; font-size: 14px;">
                        <?php _e('Com Mapeamento', 'haklai-app'); ?>
                    </div>
                </div>
                
                <div class="status-card" style="background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                    <div style="font-size: 24px; font-weight: bold; color: #ffc107;">
                        <?php echo esc_html($sync_status['unmapped_cpts']); ?>
                    </div>
                    <div style="color: #666; font-size: 14px;">
                        <?php _e('Sem Mapeamento', 'haklai-app'); ?>
                    </div>
                </div>
                
                <div class="status-card" style="background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;">
                    <div style="font-size: 24px; font-weight: bold; color: #dc3545;">
                        <?php echo esc_html($sync_status['invalid_mappings']); ?>
                    </div>
                    <div style="color: #666; font-size: 14px;">
                        <?php _e('Mapeamentos Inválidos', 'haklai-app'); ?>
                    </div>
                </div>
            </div>
            
            <!-- Status por Tipo -->
            <div style="margin-top: 20px;">
                <h4><?php _e('Status por Tipo', 'haklai-app'); ?></h4>
                <table class="wp-list-table widefat fixed striped" style="margin-top: 10px;">
                    <thead>
                        <tr>
                            <th><?php _e('Tipo', 'haklai-app'); ?></th>
                            <th><?php _e('Total', 'haklai-app'); ?></th>
                            <th><?php _e('Mapeados', 'haklai-app'); ?></th>
                            <th><?php _e('Sem Mapeamento', 'haklai-app'); ?></th>
                            <th><?php _e('Inválidos', 'haklai-app'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sync_status['by_type'] as $cpt_type => $type_status): ?>
                            <tr>
                                <td><strong><?php echo esc_html(ucfirst(str_replace('haklai_', '', $cpt_type))); ?></strong></td>
                                <td><?php echo esc_html($type_status['total']); ?></td>
                                <td>
                                    <span style="color: #28a745; font-weight: bold;">
                                        <?php echo esc_html($type_status['mapped']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: #ffc107;">
                                        <?php echo esc_html($type_status['unmapped']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: #dc3545;">
                                        <?php echo esc_html($type_status['invalid']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="notice notice-warning">
                <p><?php _e('Sistema de sincronização não disponível. Verifique se a classe Haklai_Sync_Manager está carregada.', 'haklai-app'); ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Ferramentas de Sincronização -->
    <div class="sync-tools-section" style="background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0;">
            <i class="fas fa-tools"></i>
            <?php _e('Ferramentas de Sincronização', 'haklai-app'); ?>
        </h3>
        
        <div class="tools-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
            
            <!-- Validação de Integridade -->
            <div class="tool-card" style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                <h4 style="margin-top: 0;">
                    <i class="fas fa-search" style="color: #0073aa;"></i>
                    <?php _e('Validar Integridade', 'haklai-app'); ?>
                </h4>
                <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
                    <?php _e('Verifica se todos os mapeamentos entre CPTs e tabelas MySQL estão corretos e válidos.', 'haklai-app'); ?>
                </p>
                <button type="button" id="btn-sync-validate-integrity" class="button button-secondary" style="width: 100%;">
                    <i class="fas fa-search"></i>
                    <?php _e('Executar Validação', 'haklai-app'); ?>
                </button>
            </div>
            
            <!-- Reparo de Mapeamentos -->
            <div class="tool-card" style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                <h4 style="margin-top: 0;">
                    <i class="fas fa-wrench" style="color: #28a745;"></i>
                    <?php _e('Reparar Mapeamentos', 'haklai-app'); ?>
                </h4>
                <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
                    <?php _e('Repara automaticamente mapeamentos perdidos ou inválidos, criando novos vínculos quando necessário.', 'haklai-app'); ?>
                </p>
                <button type="button" id="btn-sync-repair-mappings" class="button button-secondary" style="width: 100%;">
                    <i class="fas fa-wrench"></i>
                    <?php _e('Executar Reparo', 'haklai-app'); ?>
                </button>
            </div>
            
            <!-- Atualizar Status -->
            <div class="tool-card" style="background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
                <h4 style="margin-top: 0;">
                    <i class="fas fa-sync" style="color: #ffc107;"></i>
                    <?php _e('Atualizar Status', 'haklai-app'); ?>
                </h4>
                <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
                    <?php _e('Atualiza as estatísticas de sincronização para refletir o estado atual do sistema.', 'haklai-app'); ?>
                </p>
                <button type="button" id="btn-sync-refresh-status" class="button button-secondary" style="width: 100%;">
                    <i class="fas fa-sync"></i>
                    <?php _e('Atualizar', 'haklai-app'); ?>
                </button>
            </div>
            
        </div>
    </div>
    
    <!-- Resultados -->
    <div id="sync-results" style="display: none; background: #fff; padding: 20px; border-radius: 8px; margin-top: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0;">
            <i class="fas fa-info-circle"></i>
            <?php _e('Resultados', 'haklai-app'); ?>
        </h3>
        <div id="sync-results-content"></div>
    </div>
    
    <!-- Informações Importantes -->
    <div class="sync-info-section" style="background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; margin-top: 20px;">
        <h3 style="margin-top: 0; color: #856404;">
            <i class="fas fa-info-circle"></i>
            <?php _e('Informações Importantes', 'haklai-app'); ?>
        </h3>
        <ul style="color: #856404; margin-left: 20px;">
            <li>
                <strong><?php _e('Sincronização Automática:', 'haklai-app'); ?></strong>
                <?php _e('Todos os novos registros criados no WP Admin são sincronizados automaticamente com as tabelas MySQL.', 'haklai-app'); ?>
            </li>
            <li>
                <strong><?php _e('Mapeamento Persistente:', 'haklai-app'); ?></strong>
                <?php _e('Cada CPT armazena seu ID correspondente na tabela MySQL via meta key _haklai_table_id para busca rápida.', 'haklai-app'); ?>
            </li>
            <li>
                <strong><?php _e('Conversão de IDs:', 'haklai-app'); ?></strong>
                <?php _e('Relacionamentos (cell_id, leader_id, etc.) são convertidos automaticamente de IDs de CPT para IDs de tabela MySQL.', 'haklai-app'); ?>
            </li>
            <li>
                <strong><?php _e('Validação e Reparo:', 'haklai-app'); ?></strong>
                <?php _e('Use as ferramentas acima para validar e reparar problemas de sincronização quando necessário.', 'haklai-app'); ?>
            </li>
        </ul>
    </div>
</div>

