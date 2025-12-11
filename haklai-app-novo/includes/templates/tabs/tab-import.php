<?php
/**
 * Aba 2: Importar e Exportar
 */

if (!defined('ABSPATH')) exit;
?>

<div class="tab-section">
    <h2 class="tab-section-title"><?php _e('Importação e Exportação de Dados', 'haklai-app'); ?></h2>
    
    <!-- IMPORTAÇÃO -->
    <div class="settings-card">
        <div class="card-header-custom">
            <div class="card-icon-large import-icon">
                <i class="fas fa-file-upload"></i>
            </div>
            <div>
                <h3><?php _e('Importação de Membros', 'haklai-app'); ?></h3>
                <p><?php _e('Importe membros e líderes em massa através de arquivos CSV ou Excel', 'haklai-app'); ?></p>
            </div>
        </div>
        
        <div class="card-body-custom">
            <div class="template-download">
                <p class="info-text">
                    <i class="fas fa-info-circle"></i>
                    <?php _e('Baixe o template para garantir que seu arquivo está no formato correto:', 'haklai-app'); ?>
                </p>
                <button type="button" class="btn btn-secondary" id="haklai-download-template">
                    <i class="fas fa-download"></i>
                    <?php _e('Baixar Template CSV', 'haklai-app'); ?>
                </button>
            </div>
            
            <form id="haklai-import-form" enctype="multipart/form-data">
                <div class="file-upload-wrapper">
                    <input type="file" id="haklai-import-file" name="import_file" accept=".csv,.xlsx,.xls" required>
                    <label for="haklai-import-file" class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span class="file-name"><?php _e('Selecione um arquivo CSV ou Excel', 'haklai-app'); ?></span>
                    </label>
                </div>
                
                <div class="import-actions">
                    <button type="submit" class="btn btn-primary" id="haklai-import-submit">
                        <i class="fas fa-upload"></i>
                        <?php _e('Iniciar Importação', 'haklai-app'); ?>
                    </button>
                </div>
            </form>
            
            <div id="haklai-import-progress" class="progress-wrapper" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text"><?php _e('Processando...', 'haklai-app'); ?></div>
            </div>
            
            <div id="haklai-import-result" class="result-wrapper" style="display: none;"></div>
        </div>
    </div>
    
    <!-- EXPORTAÇÃO -->
    <div class="settings-card">
        <div class="card-header-custom">
            <div class="card-icon-large export-icon">
                <i class="fas fa-file-download"></i>
            </div>
            <div>
                <h3><?php _e('Exportação de Membros', 'haklai-app'); ?></h3>
                <p><?php _e('Exporte dados dos membros com filtros avançados', 'haklai-app'); ?></p>
            </div>
        </div>
        
        <div class="card-body-custom">
            <div class="export-filters">
                <h4><?php _e('Filtros (Opcional)', 'haklai-app'); ?></h4>
                <div class="filters-grid">
                    <div class="filter-item">
                        <label><?php _e('Célula', 'haklai-app'); ?></label>
                        <select id="export-filter-cell" class="form-control-custom">
                            <option value=""><?php _e('Todas', 'haklai-app'); ?></option>
                            <?php foreach ($cells as $cell): ?>
                                <option value="<?php echo esc_attr($cell->ID); ?>">
                                    <?php echo esc_html($cell->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label><?php _e('Status', 'haklai-app'); ?></label>
                        <select id="export-filter-status" class="form-control-custom">
                            <option value=""><?php _e('Todos', 'haklai-app'); ?></option>
                            <option value="active"><?php _e('Ativo', 'haklai-app'); ?></option>
                            <option value="inactive"><?php _e('Inativo', 'haklai-app'); ?></option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label><?php _e('Nível', 'haklai-app'); ?></label>
                        <select id="export-filter-level" class="form-control-custom">
                            <option value=""><?php _e('Todos', 'haklai-app'); ?></option>
                            <option value="0"><?php _e('Membro', 'haklai-app'); ?></option>
                            <option value="1"><?php _e('Líder', 'haklai-app'); ?></option>
                            <option value="2"><?php _e('Discipulador', 'haklai-app'); ?></option>
                        </select>
                    </div>
                </div>
                <button type="button" class="btn btn-link" id="haklai-clear-filters">
                    <i class="fas fa-times"></i>
                    <?php _e('Limpar Filtros', 'haklai-app'); ?>
                </button>
            </div>
            
            <div class="export-actions">
                <div class="format-selector">
                    <label><?php _e('Formato:', 'haklai-app'); ?></label>
                    <div class="format-options">
                        <label class="format-option">
                            <input type="radio" name="export_format" value="csv" checked>
                            <span class="format-label"><i class="fas fa-file-csv"></i> CSV</span>
                        </label>
                        <label class="format-option">
                            <input type="radio" name="export_format" value="xlsx">
                            <span class="format-label"><i class="fas fa-file-excel"></i> Excel</span>
                        </label>
                    </div>
                </div>
                
                <button type="button" class="btn btn-success" id="haklai-export-submit">
                    <i class="fas fa-download"></i>
                    <?php _e('Exportar Membros', 'haklai-app'); ?>
                </button>
            </div>
            
            <div id="haklai-export-progress" class="progress-wrapper" style="display: none;">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text"><?php _e('Gerando exportação...', 'haklai-app'); ?></div>
            </div>
            
            <div id="haklai-export-result" class="result-wrapper" style="display: none;"></div>
        </div>
    </div>
    
    <!-- Histórico de Importações -->
    <div class="settings-card">
        <div class="card-header-custom">
            <div class="card-icon-large">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <h3><?php _e('Histórico de Importações', 'haklai-app'); ?></h3>
                <p><?php _e('Últimas 10 importações realizadas', 'haklai-app'); ?></p>
            </div>
        </div>
        
        <div class="card-body-custom">
            <?php if (!empty($import_history)): ?>
                <div class="table-responsive">
                    <table class="haklai-table">
                        <thead>
                            <tr>
                                <th><?php _e('Data/Hora', 'haklai-app'); ?></th>
                                <th><?php _e('Arquivo', 'haklai-app'); ?></th>
                                <th><?php _e('Registros', 'haklai-app'); ?></th>
                                <th><?php _e('Erros', 'haklai-app'); ?></th>
                                <th><?php _e('Avisos', 'haklai-app'); ?></th>
                                <th><?php _e('Ações', 'haklai-app'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($import_history as $entry): ?>
                                <tr>
                                    <td><?php echo esc_html(date_i18n('d/m/Y H:i', $entry['timestamp'])); ?></td>
                                    <td><code><?php echo esc_html($entry['filename']); ?></code></td>
                                    <td><span class="badge badge-primary"><?php echo esc_html($entry['records']); ?></span></td>
                                    <td>
                                        <?php if ($entry['errors'] > 0): ?>
                                            <span class="badge badge-danger"><?php echo esc_html($entry['errors']); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-success">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($entry['warnings'] > 0): ?>
                                            <span class="badge badge-warning"><?php echo esc_html($entry['warnings']); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn-icon view-import-log" data-id="<?php echo esc_attr($entry['id']); ?>" title="<?php _e('Ver Log', 'haklai-app'); ?>">
                                            <i class="fas fa-file-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-data"><?php _e('Nenhuma importação realizada ainda.', 'haklai-app'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

