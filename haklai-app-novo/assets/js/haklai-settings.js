/**
 * Haklai Settings Page JavaScript - Versão Completa com 6 Abas
 * Projeto: Haklai Church — Plugin WordPress Modular
 * Desenvolvido por: Ricardo Sarmento - https://linx.pt
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // ==========================================
        // NAVEGAÇÃO DE ABAS
        // ==========================================
        
        $('.tab-button').on('click', function() {
            const tabId = $(this).data('tab');
            
            // Remove ativa de todas
            $('.tab-button').removeClass('active');
            $('.tab-content').removeClass('active');
            
            // Ativa clicada
            $(this).addClass('active');
            $('#tab-' + tabId).addClass('active');
            
            // Scroll suave para topo
            $('html, body').animate({scrollTop: $('.config-container').offset().top - 50}, 300);
        });
        
        // ==========================================
        // ABA 1: LICENÇA
        // ==========================================
        
        $('[data-tab="license"].btn-save-tab').on('click', function() {
            const licenseKey = $('#haklai-license-key').val();
            const licenseExpires = $('#haklai-license-expires').val();
            
            $.ajax({
                url: haklaiSettings.ajax_url,
                type: 'POST',
                data: {
                    action: 'haklai_save_license',
                    nonce: haklaiSettings.nonce,
                    license_key: licenseKey,
                    license_expires: licenseExpires
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.data.message);
                    } else {
                        showNotification('error', response.data.message);
                    }
                }
            });
        });
        
        // ==========================================
        // ABA 2: IMPORTAÇÃO (mantém código existente)
        // ==========================================
        
        // Atualiza label quando arquivo é selecionado
        $('#haklai-import-file').on('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'Selecione um arquivo CSV ou Excel';
            const $label = $(this).next('label');
            
            $label.find('.file-name').text(fileName);
            
            if (this.files[0]) {
                $label.addClass('has-file');
            } else {
                $label.removeClass('has-file');
            }
        });
        
        // Download do template
        $('#haklai-download-template').on('click', function(e) {
            e.preventDefault();
            window.location.href = haklaiSettings.ajax_url + '?action=haklai_download_template&nonce=' + haklaiSettings.nonce;
        });
        
        // Submit importação
        $('#haklai-import-form').on('submit', function(e) {
            e.preventDefault();
            
            const fileInput = $('#haklai-import-file')[0];
            if (!fileInput.files[0]) {
                alert('Por favor, selecione um arquivo para importar.');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'haklai_import_process');
            formData.append('nonce', haklaiSettings.nonce);
            formData.append('import_file', fileInput.files[0]);
            
            $('#haklai-import-progress').show();
            $('#haklai-import-result').hide();
            $('#haklai-import-submit').prop('disabled', true);
            
            animateProgress('#haklai-import-progress .progress-fill');
            
            $.ajax({
                url: haklaiSettings.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#haklai-import-progress').hide();
                    if (response.success) {
                        showImportSuccess(response.data);
                        // Recarrega histórico
                        location.reload();
                    } else {
                        showImportError(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    $('#haklai-import-progress').hide();
                    showImportError({message: 'Erro: ' + error});
                },
                complete: function() {
                    $('#haklai-import-submit').prop('disabled', false);
                }
            });
        });
        
        // Exportação
        $('#haklai-clear-filters').on('click', function() {
            $('.export-filter').val('');
        });
        
        $('#haklai-export-submit').on('click', function(e) {
            e.preventDefault();
            
            const format = $('input[name="export_format"]:checked').val();
            const filters = {
                cell_id: $('#export-filter-cell').val(),
                status: $('#export-filter-status').val(),
                role_level: $('#export-filter-level').val(),
                baptism_status: $('#export-filter-baptism').val(),
                date_from: $('#export-filter-date-from').val(),
                date_to: $('#export-filter-date-to').val()
            };
            
            $('#haklai-export-progress').show();
            $('#haklai-export-result').hide();
            $('#haklai-export-submit').prop('disabled', true);
            
            animateProgress('#haklai-export-progress .progress-fill');
            
            $.ajax({
                url: haklaiSettings.ajax_url,
                type: 'POST',
                data: {
                    action: 'haklai_export_process',
                    nonce: haklaiSettings.nonce,
                    format: format,
                    filters: filters
                },
                success: function(response) {
                    $('#haklai-export-progress').hide();
                    if (response.success) {
                        showExportSuccess(response.data);
                    } else {
                        showExportError(response.data);
                    }
                },
                complete: function() {
                    $('#haklai-export-submit').prop('disabled', false);
                }
            });
        });
        
        // Ver log de importação (Modal)
        $('.view-import-log').on('click', function() {
            const entryId = $(this).data('id');
            
            $.ajax({
                url: haklaiSettings.ajax_url,
                type: 'POST',
                data: {
                    action: 'haklai_get_import_log',
                    nonce: haklaiSettings.nonce,
                    entry_id: entryId
                },
                success: function(response) {
                    if (response.success) {
                        showImportLogModal(response.data.entry);
                    }
                }
            });
        });
        
        // ==========================================
        // ABA 3: EMAIL TEMPLATES
        // ==========================================
        
        // Salvar template de email
        $('.save-email-template').on('click', function() {
            const templateKey = $(this).data('template');
            const subject = $(`.email-subject[data-template="${templateKey}"]`).val();
            const body = $(`.email-body[data-template="${templateKey}"]`).val();
            
            $.ajax({
                url: haklaiSettings.ajax_url,
                type: 'POST',
                data: {
                    action: 'haklai_save_email_template',
                    nonce: haklaiSettings.nonce,
                    template_key: templateKey,
                    subject: subject,
                    body: body
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.data.message);
                    }
                }
            });
        });
        
        // Enviar email de teste
        $('.test-email-template').on('click', function() {
            const templateKey = $(this).data('template');
            const testEmail = prompt('Digite o email para teste:');
            
            if (!testEmail) return;
            
            $.ajax({
                url: haklaiSettings.ajax_url,
                type: 'POST',
                data: {
                    action: 'haklai_test_email',
                    nonce: haklaiSettings.nonce,
                    template_key: templateKey,
                    test_email: testEmail
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.data.message);
                    } else {
                        showNotification('error', response.data.message);
                    }
                }
            });
        });
        
        // ==========================================
        // ABA 4: PERMISSÕES
        // ==========================================
        
        // Salvar permissões de uma role
        $('.save-role-permissions').on('click', function() {
            const roleSlug = $(this).data('role');
            const capabilities = {};
            
            $(`.capability-checkbox[data-role="${roleSlug}"]`).each(function() {
                const capKey = $(this).data('capability');
                capabilities[capKey] = $(this).is(':checked');
            });
            
            $.ajax({
                url: haklaiSettings.ajax_url,
                type: 'POST',
                data: {
                    action: 'haklai_save_capabilities',
                    nonce: haklaiSettings.nonce,
                    role_slug: roleSlug,
                    capabilities: capabilities
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.data.message);
                    }
                }
            });
        });
        
        // ==========================================
        // ABA 6: CONFIGURAÇÕES GERAIS
        // ==========================================
        
        // Upload de logo
        $('#haklai-upload-logo').on('click', function(e) {
            e.preventDefault();
            
            const mediaUploader = wp.media({
                title: 'Selecionar Logo',
                button: { text: 'Usar esta imagem' },
                multiple: false
            });
            
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#haklai-church-logo').val(attachment.url);
                
                if ($('.logo-preview').length) {
                    $('.logo-preview img').attr('src', attachment.url);
                } else {
                    $('.logo-upload').prepend(`
                        <div class="logo-preview">
                            <img src="${attachment.url}" alt="Logo" id="haklai-logo-preview">
                        </div>
                    `);
                }
                
                showNotification('success', 'Logo selecionado! Clique em Salvar para aplicar.');
            });
            
            mediaUploader.open();
        });
        
        // Remover logo
        $('#haklai-remove-logo').on('click', function() {
            if (confirm('Remover logo?')) {
                $('#haklai-church-logo').val('');
                $('.logo-preview').remove();
                showNotification('success', 'Logo removido! Clique em Salvar para aplicar.');
            }
        });
        
        // Color picker - atualiza preview em tempo real
        $('.color-picker').on('input', function() {
            const color = $(this).val();
            $(this).next('.color-hex').val(color);
            updateColorPreview();
        });
        
        // Restaurar cores padrão
        $('#haklai-restore-default-colors').on('click', function() {
            $('#haklai-primary-color').val('#667eea').trigger('input');
            $('#haklai-secondary-color').val('#764ba2').trigger('input');
            $('#haklai-background-color').val('#f7fafc').trigger('input');
        });
        
        // Salvar configurações gerais
        $('[data-tab="general"].btn-save-tab').on('click', function() {
            const churchName = $('#haklai-church-name').val();
            const churchLogo = $('#haklai-church-logo').val();
            const primaryColor = $('#haklai-primary-color').val();
            const secondaryColor = $('#haklai-secondary-color').val();
            const backgroundColor = $('#haklai-background-color').val();
            const themeMode = $('#haklai-theme-mode').val();
            
            $.ajax({
                url: haklaiSettings.ajax_url,
                type: 'POST',
                data: {
                    action: 'haklai_save_general',
                    nonce: haklaiSettings.nonce,
                    church_name: churchName,
                    church_logo: churchLogo,
                    primary_color: primaryColor,
                    secondary_color: secondaryColor,
                    background_color: backgroundColor,
                    theme_mode: themeMode
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.data.message);
                    }
                }
            });
        });
        
        // ==========================================
        // FUNÇÕES AUXILIARES
        // ==========================================
        
        function updateColorPreview() {
            const primary = $('#haklai-primary-color').val();
            const secondary = $('#haklai-secondary-color').val();
            const background = $('#haklai-background-color').val();
            
            // Atualiza botões
            $('.preview-btn-primary').css('background', `linear-gradient(135deg, ${primary} 0%, ${secondary} 100%)`);
            $('.preview-btn-secondary').css('background', `linear-gradient(135deg, #6b7280 0%, #4b5563 100%)`);
            
            // Atualiza cards
            $('.preview-card-header').css('background', `linear-gradient(135deg, ${primary} 0%, ${secondary} 100%)`);
            $('.preview-card-body').css('background', background);
            $('.stat-num').css('color', primary);
            $('.member-badge').css('background', primary);
        }
        
        // Inicializa preview
        updateColorPreview();
        
        function showImportSuccess(data) {
            const html = `
                <div class="result-success">
                    <div class="result-icon">✅</div>
                    <div class="result-title">Importação Concluída!</div>
                    <div class="result-message">
                        <strong>${data.imported}</strong> registro(s) importado(s).
                        ${data.errors > 0 ? `<br><strong>${data.errors}</strong> erro(s).` : ''}
                        ${data.warnings > 0 ? `<br><strong>${data.warnings}</strong> aviso(s).` : ''}
                    </div>
                </div>
            `;
            $('#haklai-import-result').html(html).show();
            $('#haklai-import-form')[0].reset();
            $('.file-upload-label').removeClass('has-file').find('.file-name').text('Selecione um arquivo CSV ou Excel');
        }
        
        function showImportError(data) {
            const html = `
                <div class="result-error">
                    <div class="result-icon">❌</div>
                    <div class="result-title">Erro na Importação</div>
                    <div class="result-message">${data.message || 'Erro ao processar arquivo'}</div>
                </div>
            `;
            $('#haklai-import-result').html(html).show();
        }
        
        function showExportSuccess(data) {
            const html = `
                <div class="result-success">
                    <div class="result-icon">✅</div>
                    <div class="result-title">Exportação Concluída!</div>
                    <div class="result-message">
                        <strong>${data.total_records}</strong> registro(s) exportado(s).
                    </div>
                    <div style="margin-top: 20px;">
                        <a href="${data.file_url}" class="btn btn-success" download>
                            <i class="fas fa-download"></i> Baixar Arquivo ${data.format.toUpperCase()}
                        </a>
                    </div>
                </div>
            `;
            $('#haklai-export-result').html(html).show();
        }
        
        function showExportError(data) {
            const html = `
                <div class="result-error">
                    <div class="result-icon">❌</div>
                    <div class="result-title">Erro na Exportação</div>
                    <div class="result-message">${data.message || 'Erro ao gerar arquivo'}</div>
                </div>
            `;
            $('#haklai-export-result').html(html).show();
        }
        
        function animateProgress(selector) {
            const $fill = $(selector);
            $fill.css('width', '0%');
            
            let progress = 0;
            const interval = setInterval(function() {
                progress += Math.random() * 15;
                if (progress > 90) {
                    progress = 90;
                    clearInterval(interval);
                }
                $fill.css('width', progress + '%');
            }, 300);
        }
        
        function showImportLogModal(entry) {
            let logHtml = `
                <div class="log-summary">
                    <p><strong>Arquivo:</strong> ${entry.filename}</p>
                    <p><strong>Data:</strong> ${new Date(entry.timestamp * 1000).toLocaleString('pt-BR')}</p>
                    <p><strong>Registros:</strong> ${entry.records}</p>
                    <p><strong>Erros:</strong> ${entry.errors}</p>
                    <p><strong>Avisos:</strong> ${entry.warnings}</p>
                </div>
            `;
            
            if (entry.log && entry.log.errors && entry.log.errors.length > 0) {
                logHtml += '<h4 style="margin-top: 20px; color: #dc2626;">Erros:</h4><ul>';
                entry.log.errors.forEach(err => {
                    logHtml += `<li>Linha ${err.row} (${err.name}): ${err.error}</li>`;
                });
                logHtml += '</ul>';
            }
            
            if (entry.log && entry.log.warnings && entry.log.warnings.length > 0) {
                logHtml += '<h4 style="margin-top: 20px; color: #f59e0b;">Avisos:</h4><ul>';
                entry.log.warnings.forEach(warn => {
                    logHtml += `<li>${warn}</li>`;
                });
                logHtml += '</ul>';
            }
            
            $('#haklai-modal-log-content').html(logHtml);
            $('#haklai-import-log-modal').addClass('active').fadeIn(200);
        }
        
        // Fechar modal
        $('.haklai-modal-close, .haklai-modal-overlay').on('click', function() {
            $('.haklai-modal').removeClass('active').fadeOut(200);
        });
        
        // ==========================================
        // ABA 6: SINCRONIZAÇÃO
        // ==========================================
        
        // Validar Integridade
        $('#btn-sync-validate-integrity').on('click', function() {
            const $btn = $(this);
            const originalText = $btn.html();
            
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Validando...');
            
            $.ajax({
                url: haklaiSettings.ajax_url,
                type: 'POST',
                data: {
                    action: 'haklai_sync_validate_integrity',
                    nonce: haklaiSettings.nonce
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);
                    
                    if (response.success) {
                        const data = response.data;
                        let html = '<div style="line-height: 1.8;">';
                        html += '<strong>Resultado da Validação:</strong><br>';
                        html += `Total verificado: <strong>${data.total_checked}</strong><br>`;
                        html += `Erros encontrados: <strong style="color: #dc3545;">${data.errors.length}</strong><br>`;
                        html += `Reparados automaticamente: <strong style="color: #28a745;">${data.fixed}</strong><br>`;
                        
                        if (data.errors.length > 0) {
                            html += '<br><strong>Erros encontrados:</strong><ul style="margin-left: 20px; margin-top: 10px;">';
                            data.errors.forEach(function(error) {
                                html += `<li style="color: #dc3545;">${error.message}</li>`;
                            });
                            html += '</ul>';
                        } else {
                            html += '<br><span style="color: #28a745;"><strong>✓ Nenhum erro encontrado! Todos os mapeamentos estão válidos.</strong></span>';
                        }
                        
                        html += '</div>';
                        
                        $('#sync-results-content').html(html);
                        $('#sync-results').slideDown();
                        
                        showNotification('success', 'Validação concluída com sucesso!');
                    } else {
                        showNotification('error', response.data?.message || 'Erro ao validar integridade');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalText);
                    showNotification('error', 'Erro de conexão ao validar integridade');
                }
            });
        });
        
        // Reparar Mapeamentos
        $('#btn-sync-repair-mappings').on('click', function() {
            if (!confirm('Tem certeza que deseja reparar os mapeamentos? Esta ação pode levar alguns minutos.')) {
                return;
            }
            
            const $btn = $(this);
            const originalText = $btn.html();
            
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Reparando...');
            
            $.ajax({
                url: haklaiSettings.ajax_url,
                type: 'POST',
                data: {
                    action: 'haklai_sync_repair_mappings',
                    nonce: haklaiSettings.nonce
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);
                    
                    if (response.success) {
                        const data = response.data;
                        let html = '<div style="line-height: 1.8;">';
                        html += '<strong>Resultado do Reparo:</strong><br>';
                        html += `Registros reparados: <strong style="color: #28a745;">${data.repaired}</strong><br>`;
                        html += `Erros: <strong style="color: #dc3545;">${data.errors}</strong><br>`;
                        html += '</div>';
                        
                        $('#sync-results-content').html(html);
                        $('#sync-results').slideDown();
                        
                        showNotification('success', data.message || 'Reparo concluído com sucesso!');
                        
                        // Atualiza status após 1 segundo
                        setTimeout(function() {
                            $('#btn-sync-refresh-status').click();
                        }, 1000);
                    } else {
                        showNotification('error', response.data?.message || 'Erro ao reparar mapeamentos');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalText);
                    showNotification('error', 'Erro de conexão ao reparar mapeamentos');
                }
            });
        });
        
        // Atualizar Status
        $('#btn-sync-refresh-status').on('click', function() {
            const $btn = $(this);
            const originalText = $btn.html();
            
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Atualizando...');
            
            $.ajax({
                url: haklaiSettings.ajax_url,
                type: 'POST',
                data: {
                    action: 'haklai_sync_get_status',
                    nonce: haklaiSettings.nonce
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalText);
                    
                    if (response.success) {
                        // Recarrega a página para atualizar os dados
                        location.reload();
                    } else {
                        showNotification('error', response.data?.message || 'Erro ao obter status');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalText);
                    showNotification('error', 'Erro de conexão ao atualizar status');
                }
            });
        });
        
        function showNotification(type, message) {
            const icon = type === 'success' ? '✅' : '❌';
            const bgColor = type === 'success' ? '#10b981' : '#ef4444';
            
            const $notification = $(`
                <div class="haklai-notification" style="
                    position: fixed;
                    top: 32px;
                    right: 20px;
                    z-index: 99999;
                    background: ${bgColor};
                    color: white;
                    padding: 15px 25px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    font-weight: 600;
                ">
                    <span style="font-size: 1.2rem;">${icon}</span>
                    <span>${message}</span>
                </div>
            `);
            
            $('body').append($notification);
            
            setTimeout(function() {
                $notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    });
    
})(jQuery);
