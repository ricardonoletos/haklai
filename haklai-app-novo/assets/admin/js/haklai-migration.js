/**
 * JavaScript para Página de Migração de Dados
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

(function($) {
    'use strict';
    
    const MigrationManager = {
        init: function() {
            this.bindEvents();
            this.loadStats();
        },
        
        bindEvents: function() {
            $('#btn-start-migration').on('click', () => this.startMigration());
            $('#btn-validate').on('click', () => this.validateData());
            $('#btn-export-backup').on('click', () => this.exportBackup());
            
            // Novos handlers com sistema de sincronização melhorado
            $('#btn-validate-integrity').on('click', () => this.validateIntegrity());
            $('#btn-repair-mappings').on('click', () => this.repairMappings());
            $('#btn-get-sync-status').on('click', () => this.getSyncStatus());
        },
        
        loadStats: function() {
            $.ajax({
                url: haklaiMigration.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'haklai_get_migration_stats',
                    nonce: haklaiMigration.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Atualiza estatísticas se necessário
                        console.log('Estatísticas carregadas:', response.data);
                    }
                }
            });
        },
        
        validateData: function() {
            this.addLog('info', 'Validando dados antes da migração...');
            
            $.ajax({
                url: haklaiMigration.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'haklai_validate_migration',
                    nonce: haklaiMigration.nonce
                },
                success: (response) => {
                    if (response.success && response.data.valid) {
                        this.addLog('success', 'Validação concluída com sucesso!');
                        alert('Dados validados com sucesso!');
                    } else {
                        this.addLog('error', 'Erros encontrados na validação: ' + response.data.messages.join(', '));
                        alert('Erros encontrados na validação. Verifique os logs.');
                    }
                },
                error: () => {
                    this.addLog('error', 'Erro ao validar dados.');
                }
            });
        },
        
        exportBackup: function() {
            this.addLog('info', 'Exportando backup dos dados...');
            
            $.ajax({
                url: haklaiMigration.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'haklai_export_backup',
                    nonce: haklaiMigration.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.addLog('success', 'Backup exportado com sucesso!');
                        alert('Backup exportado com sucesso!');
                    }
                },
                error: () => {
                    this.addLog('error', 'Erro ao exportar backup.');
                }
            });
        },
        
        startMigration: function() {
            if (!confirm('Tem certeza que deseja iniciar a migração? Esta operação pode levar alguns minutos.')) {
                return;
            }
            
            $('#btn-start-migration').prop('disabled', true).text('Migrando...');
            $('#migration-progress').show();
            
            this.addLog('info', 'Iniciando migração de dados...');
            this.addLog('info', 'Migração será executada em lotes para evitar timeouts.');
            
            // Reseta contadores
            let totalMigrated = 0;
            let totalErrors = 0;
            
            // Ordem: 1. Células, 2. Membros, 3. Reuniões
            const migrationOrder = ['cells', 'members', 'meetings'];
            let currentTypeIndex = 0;
            
            const migrateType = (type, offset = 0) => {
                $.ajax({
                    url: haklaiMigration.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'haklai_migrate_batch',
                        type: type,
                        offset: offset,
                        batch_size: haklaiMigration.batchSize,
                        nonce: haklaiMigration.nonce
                    },
                    success: (response) => {
                        if (response.success) {
                            const result = response.data;
                            
                            // Atualiza contadores
                            totalMigrated += result.success;
                            totalErrors += result.errors;
                            
                            // Atualiza progresso
                            this.updateProgress(totalMigrated, totalErrors, result);
                            
                            // Adiciona logs
                            result.messages.forEach(msg => {
                                if (msg.includes('sucesso')) {
                                    this.addLog('success', msg);
                                } else {
                                    this.addLog('error', msg);
                                }
                            });
                            
                            // Se há mais, continua
                            if (result.has_more) {
                                setTimeout(() => {
                                    migrateType(type, offset + result.processed);
                                }, 500); // Pequeno delay entre lotes
                            } else {
                                // Próximo tipo
                                currentTypeIndex++;
                                if (currentTypeIndex < migrationOrder.length) {
                                    this.addLog('info', `Migração de ${this.getTypeLabel(type)} concluída. Iniciando migração de ${this.getTypeLabel(migrationOrder[currentTypeIndex])}...`);
                                    setTimeout(() => {
                                        migrateType(migrationOrder[currentTypeIndex], 0);
                                    }, 1000);
                                } else {
                                    // Migração completa
                                    this.completeMigration(totalMigrated, totalErrors);
                                }
                            }
                        } else {
                            this.addLog('error', 'Erro na migração: ' + (response.data?.message || 'Erro desconhecido'));
                            this.completeMigration(totalMigrated, totalErrors, true);
                        }
                    },
                    error: (xhr) => {
                        this.addLog('error', 'Erro de conexão durante a migração.');
                        this.completeMigration(totalMigrated, totalErrors, true);
                    }
                });
            };
            
            // Inicia migração
            migrateType(migrationOrder[0], 0);
        },
        
        getTypeLabel: function(type) {
            const labels = {
                'cells': 'Células',
                'members': 'Membros',
                'meetings': 'Reuniões'
            };
            return labels[type] || type;
        },
        
        updateProgress: function(migrated, errors, result) {
            // Atualiza contadores
            $('#progress-migrated').text(migrated);
            $('#progress-errors').text(errors);
            
            // Calcula porcentagem (aproximada, baseada nos dados atuais)
            const stats = this.getCurrentStats();
            const total = stats.total;
            const percentage = total > 0 ? Math.round((migrated / total) * 100) : 0;
            
            $('#progress-fill').css('width', percentage + '%');
            $('#progress-percentage').text(percentage + '%');
            $('#progress-details').text(`Migrando ${this.getTypeLabel(result.type)}... (${result.processed} processados neste lote)`);
            
            // Atualiza restantes
            const remaining = Math.max(0, total - migrated);
            $('#progress-remaining').text(remaining);
        },
        
        getCurrentStats: function() {
            // Busca estatísticas atuais
            let total = 0;
            $('.stat-card').each(function() {
                const h3 = $(this).find('h3').text();
                if (!isNaN(h3)) {
                    total += parseInt(h3);
                }
            });
            return { total: total };
        },
        
        completeMigration: function(migrated, errors, hasErrors = false) {
            $('#btn-start-migration').prop('disabled', false).html('<i class="fas fa-check"></i> Migração Concluída');
            
            if (hasErrors) {
                this.addLog('warning', 'Migração concluída com alguns erros.');
            } else {
                this.addLog('success', `Migração concluída com sucesso! ${migrated} itens migrados, ${errors} erros.`);
            }
            
            $('#progress-details').text('Migração concluída!');
            
            // Recarrega estatísticas
            setTimeout(() => {
                location.reload();
            }, 3000);
        },
        
        addLog: function(type, message) {
            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };
            
            const icon = icons[type] || 'fa-info-circle';
            const timestamp = new Date().toLocaleTimeString('pt-BR');
            
            const logEntry = $(`
                <p class="log-entry ${type}">
                    <i class="fas ${icon}"></i>
                    <span>[${timestamp}]</span> ${message}
                </p>
            `);
            
            $('#migration-logs').append(logEntry);
            
            // Auto-scroll para o último log
            const logsContainer = $('#migration-logs');
            logsContainer.scrollTop(logsContainer[0].scrollHeight);
        },
        
        /**
         * Valida integridade dos mapeamentos
         */
        validateIntegrity: function() {
            const $btn = $('#btn-validate-integrity');
            const originalText = $btn.html();
            
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Validando...');
            this.addLog('info', 'Validando integridade dos mapeamentos CPT ↔ Tabela...');
            
            $.ajax({
                url: haklaiMigration.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'haklai_validate_integrity',
                    nonce: haklaiMigration.nonce
                },
                success: (response) => {
                    $btn.prop('disabled', false).html(originalText);
                    
                    if (response.success) {
                        const data = response.data;
                        let html = '<div style="line-height: 1.8; padding: 15px;">';
                        html += `<strong>Resultado da Validação:</strong><br>`;
                        html += `Total verificado: <strong>${data.total_checked}</strong><br>`;
                        html += `Erros encontrados: <strong style="color: #dc3545;">${data.errors.length}</strong><br>`;
                        html += `Reparados automaticamente: <strong style="color: #28a745;">${data.fixed}</strong><br>`;
                        
                        if (data.errors.length > 0) {
                            html += '<br><strong>Erros encontrados:</strong><ul style="margin-left: 20px; margin-top: 10px;">';
                            data.errors.forEach((error) => {
                                html += `<li style="color: #dc3545;">${error.message}</li>`;
                            });
                            html += '</ul>';
                        } else {
                            html += '<br><span style="color: #28a745;"><strong>✓ Nenhum erro encontrado! Todos os mapeamentos estão válidos.</strong></span>';
                        }
                        html += '</div>';
                        
                        $('#validation-details').html(html);
                        $('#validation-results').slideDown();
                        
                        this.addLog('success', `Validação concluída: ${data.total_checked} verificados, ${data.errors.length} erros, ${data.fixed} reparados`);
                    } else {
                        this.addLog('error', response.data?.message || 'Erro ao validar integridade');
                    }
                },
                error: () => {
                    $btn.prop('disabled', false).html(originalText);
                    this.addLog('error', 'Erro de conexão ao validar integridade');
                }
            });
        },
        
        /**
         * Repara mapeamentos perdidos ou inválidos
         */
        repairMappings: function() {
            if (!confirm('Tem certeza que deseja reparar os mapeamentos? Esta ação pode levar alguns minutos.')) {
                return;
            }
            
            const $btn = $('#btn-repair-mappings');
            const originalText = $btn.html();
            
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Reparando...');
            this.addLog('info', 'Iniciando reparo de mapeamentos...');
            
            $.ajax({
                url: haklaiMigration.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'haklai_repair_mappings',
                    nonce: haklaiMigration.nonce
                },
                success: (response) => {
                    $btn.prop('disabled', false).html(originalText);
                    
                    if (response.success) {
                        const data = response.data;
                        this.addLog('success', `Reparo concluído: ${data.repaired} registros reparados, ${data.errors} erros`);
                        alert(data.message || 'Reparo concluído com sucesso!');
                        
                        // Recarrega estatísticas após 1 segundo
                        setTimeout(() => {
                            this.loadStats();
                        }, 1000);
                    } else {
                        this.addLog('error', response.data?.message || 'Erro ao reparar mapeamentos');
                    }
                },
                error: () => {
                    $btn.prop('disabled', false).html(originalText);
                    this.addLog('error', 'Erro de conexão ao reparar mapeamentos');
                }
            });
        },
        
        /**
         * Obtém status de sincronização
         */
        getSyncStatus: function() {
            const $btn = $('#btn-get-sync-status');
            const originalText = $btn.html();
            
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Carregando...');
            
            $.ajax({
                url: haklaiMigration.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'haklai_get_sync_status',
                    nonce: haklaiMigration.nonce
                },
                success: (response) => {
                    $btn.prop('disabled', false).html(originalText);
                    
                    if (response.success) {
                        const status = response.data;
                        let html = '<div style="line-height: 1.8; padding: 15px;">';
                        html += `<strong>Status de Sincronização:</strong><br><br>`;
                        html += `Total de CPTs: <strong>${status.total_cpts}</strong><br>`;
                        html += `Com mapeamento: <strong style="color: #28a745;">${status.mapped_cpts}</strong><br>`;
                        html += `Sem mapeamento: <strong style="color: #ffc107;">${status.unmapped_cpts}</strong><br>`;
                        html += `Mapeamentos inválidos: <strong style="color: #dc3545;">${status.invalid_mappings}</strong><br>`;
                        html += '<br><strong>Por Tipo:</strong><br>';
                        html += '<table style="width: 100%; margin-top: 10px; border-collapse: collapse;">';
                        html += '<tr style="background: #f9f9f9;"><th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Tipo</th><th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Total</th><th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Mapeados</th><th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Sem Mapeamento</th><th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Inválidos</th></tr>';
                        
                        Object.keys(status.by_type).forEach((cptType) => {
                            const typeStatus = status.by_type[cptType];
                            html += `<tr>`;
                            html += `<td style="padding: 8px; border: 1px solid #ddd;"><strong>${cptType.replace('haklai_', '')}</strong></td>`;
                            html += `<td style="padding: 8px; border: 1px solid #ddd;">${typeStatus.total}</td>`;
                            html += `<td style="padding: 8px; border: 1px solid #ddd; color: #28a745;">${typeStatus.mapped}</td>`;
                            html += `<td style="padding: 8px; border: 1px solid #ddd; color: #ffc107;">${typeStatus.unmapped}</td>`;
                            html += `<td style="padding: 8px; border: 1px solid #ddd; color: #dc3545;">${typeStatus.invalid}</td>`;
                            html += `</tr>`;
                        });
                        
                        html += '</table></div>';
                        
                        $('#validation-details').html(html);
                        $('#validation-results').slideDown();
                        
                        this.addLog('info', 'Status de sincronização atualizado');
                    } else {
                        this.addLog('error', response.data?.message || 'Erro ao obter status');
                    }
                },
                error: () => {
                    $btn.prop('disabled', false).html(originalText);
                    this.addLog('error', 'Erro de conexão ao obter status');
                }
            });
        }
    };
    
    // Inicializa quando o documento estiver pronto
    $(document).ready(function() {
        MigrationManager.init();
    });
    
})(jQuery);

