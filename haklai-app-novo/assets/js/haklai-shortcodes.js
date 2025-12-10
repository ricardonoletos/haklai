/**
 * Haklai Reports JavaScript
 * Funcionalidade para geração e gerenciamento de relatórios
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

(function($) {
    'use strict';
    
    /**
     * Objeto global para gerenciamento de relatórios
     */
    window.HaklaiReports = {
        /**
         * Gera relatório
         */
        generateReport: function(type, format) {
            const filters = this.getFilters();
            
            // Mostra loading
            this.showLoading('Gerando relatório...');
            
            $.ajax({
                url: window.haklaiReports.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'haklai_ajax_handler',
                    action_type: 'generate_report',
                    data: {
                        report_type: type,
                        format: format,
                        filters: filters
                    },
                    nonce: window.haklaiReports.nonce
                },
                success: (response) => {
                    this.hideLoading();
                    
                    console.log('Generate Report Response:', response);
                    
                    if (response.success) {
                        if (response.data && response.data.report_url) {
                            // Abre relatório em nova aba
                           // window.open(response.data.report_url, '_blank');
                            this.showNotification('Relatório gerado com sucesso!', 'success');
                            
                            // Recarrega histórico após 1.5 segundos
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            this.showNotification('Relatório gerado, mas URL não disponível', 'warning');
                        }
                    } else {
                        this.showNotification(response.message || 'Erro ao gerar relatório', 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.hideLoading();
                    console.error('Erro AJAX ao gerar relatório:', {
                        status: xhr.status,
                        statusText: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    this.showNotification('Erro de conexão. Tente novamente.', 'error');
                }
            });
        },

        /**
         * Baixa relatório para impressão
         */
        downloadReport: function(reportId) {
            console.log('Baixando relatório:', reportId);
            
            this.showLoading('Preparando relatório para impressão...');
            
            // Cria um formulário temporário para envio POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.haklaiReports.ajaxUrl;
            form.target = '_blank';
            form.style.display = 'none';
            
            // Adiciona os campos
            const fields = {
                action: 'haklai_ajax_handler',
                action_type: 'download_report',
                nonce: window.haklaiReports.nonce,
                report_id: reportId
            };
            
            Object.keys(fields).forEach(key => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            
            this.hideLoading();
            this.showNotification('Relatório aberto para impressão!', 'success');
        },
        
        /**
         * Visualiza relatório (abre em nova aba com filtros)
         */
        viewReport: function(type) {
            const filters = this.getFilters();
            
            // Valida filtros
            if (!filters.start_date || !filters.end_date) {
                this.showNotification('Por favor, selecione um período válido.', 'error');
                return;
            }
            
            // Obtém URL base do site
            const siteUrl = window.haklaiReports?.siteUrl || window.location.origin;
            
            // Constrói URL com parâmetros
            const params = new URLSearchParams({
                report_type: type,
                start_date: filters.start_date,
                end_date: filters.end_date,
                cell_id: filters.cell_id || ''
            });
            
            // Monta URL completa para /relatorio/
            const reportUrl = `${siteUrl}/relatorio/?${params.toString()}`;
            
            // Abre em nova aba
            window.open(reportUrl, '_blank');
        },
        
        /**
         * Deleta relatório
         */
        deleteReport: function(reportId) {
            if (!confirm('Tem certeza que deseja excluir este relatório?')) {
                return;
            }
            
            this.showLoading('Excluindo relatório...');
            
            $.ajax({
                url: window.haklaiReports.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'haklai_ajax_handler',
                    action_type: 'delete_report',
                    data: {
                        report_id: reportId
                    },
                    nonce: window.haklaiReports.nonce
                },
                success: (response) => {
                    this.hideLoading();
                    
                    if (response.success) {
                        this.showNotification('Relatório excluído com sucesso!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        this.showNotification(response.message || 'Erro ao excluir relatório', 'error');
                    }
                },
                error: (xhr) => {
                    this.hideLoading();
                    console.error('Erro ao excluir relatório:', xhr);
                    this.showNotification('Erro de conexão. Tente novamente.', 'error');
                }
            });
        },
        
        /**
         * Obtém filtros atuais
         */
        getFilters: function() {
            return {
                period: $('#reportPeriod').val() || '30',
                start_date: $('#reportStartDate').val() || '',
                end_date: $('#reportEndDate').val() || '',
                filter_level: $('#reportFilterLevel').val() || '',
                filter_id: $('#reportFilterId').val() || '',
                cell_id: $('#reportCellFilter').val() || ''
            };
        },
        
        /**
         * Carrega opções do filtro baseado no nível selecionado
         */
        loadFilterOptions: function(level) {
            const $filterId = $('#reportFilterId');
            $filterId.html('<option value="">Carregando...</option>');
            
            if (!level) {
                $filterId.html('<option value="">Selecione primeiro o nível</option>');
                return;
            }
            
            $.ajax({
                url: window.haklaiReports.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'haklai_ajax_handler',
                    action_type: 'get_filter_options',
                    filter_level: level,
                    nonce: window.haklaiReports.nonce
                },
                success: (response) => {
                    if (response.success && response.data && response.data.options) {
                        let html = '<option value="">Selecione...</option>';
                        response.data.options.forEach(option => {
                            html += `<option value="${option.id}">${option.name}</option>`;
                        });
                        $filterId.html(html);
                    } else {
                        $filterId.html('<option value="">Nenhuma opção disponível</option>');
                    }
                },
                error: () => {
                    $filterId.html('<option value="">Erro ao carregar opções</option>');
                }
            });
        },
        
        /**
         * Busca relatórios filtrados
         */
        filterReports: function() {
            const filters = this.getFilters();
            
            // Valida filtros
            if (!filters.start_date || !filters.end_date) {
                this.showNotification('Por favor, selecione um período válido.', 'error');
                return;
            }
            
            this.showLoading('Buscando relatórios...');
            
            $.ajax({
                url: window.haklaiReports.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'haklai_get_filtered_reports',
                    filter_level: filters.filter_level || '',
                    filter_id: filters.filter_id || '',
                    start_date: filters.start_date,
                    end_date: filters.end_date,
                    nonce: window.haklaiReports.nonce
                },
                success: (response) => {
                    this.hideLoading();
                    
                    if (response.success && response.data) {
                        this.updateReportsTable(response.data.reports, response.data.stats);
                        this.showNotification('Relatórios filtrados com sucesso!', 'success');
                    } else {
                        this.showNotification(response.data?.message || 'Erro ao buscar relatórios', 'error');
                    }
                },
                error: (xhr) => {
                    this.hideLoading();
                    console.error('Erro ao buscar relatórios:', xhr);
                    this.showNotification('Erro de conexão. Tente novamente.', 'error');
                }
            });
        },
        
        /**
         * Atualiza tabela de relatórios
         */
        updateReportsTable: function(reports, stats) {
            const $tbody = $('.recent-reports tbody');
            $tbody.empty();
            
            if (reports.length === 0) {
                $tbody.html('<tr><td colspan="7" class="text-center">Nenhum relatório encontrado para os filtros selecionados.</td></tr>');
                return;
            }
            
            reports.forEach(report => {
                const row = `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="report-icon-small presence">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">${report.cell_name || 'N/A'}</div>
                                    <small class="text-muted">Célula: ${report.cell_id || ''}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold">${report.formatted_date}</div>
                        </td>
                        <td>${report.total_members}</td>
                        <td>0</td>
                        <td>${report.attendance_rate.toFixed(2)}%</td>
                        <td><span class="badge bg-success">Concluído</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="HaklaiReports.viewReport('attendance')">
                                <i class="fas fa-eye"></i> Ver
                            </button>
                        </td>
                    </tr>
                `;
                $tbody.append(row);
            });
        },
        
        /**
         * Limpa filtros
         */
        resetFilters: function() {
            $('#reportFilterLevel').val('');
            $('#reportFilterId').html('<option value="">Selecione primeiro o nível</option>');
            $('#reportPeriod').val('30');
            $('#reportStartDate').val('');
            $('#reportEndDate').val('');
            
            // Recarrega a página para mostrar todos os relatórios
            window.location.reload();
        },
        
        /**
         * Mostra loading overlay
         */
        showLoading: function(message) {
            // Remove loading anterior se existir
            $('.haklai-loading-overlay').remove();
            
            const $loading = $(`
                <div class="haklai-loading-overlay">
                    <div class="haklai-loading-spinner">
                        <i class="fas fa-spinner fa-spin fa-3x"></i>
                        <p>${message}</p>
                    </div>
                </div>
            `);
            
            $('body').append($loading);
        },
        
        /**
         * Esconde loading overlay
         */
        hideLoading: function() {
            $('.haklai-loading-overlay').fadeOut(300, function() {
                $(this).remove();
            });
        },
        
        /**
         * Mostra notificação
         */
        showNotification: function(message, type = 'info') {
            // Tenta usar notificação do HaklaiApp se disponível
            if (window.haklaiApp && typeof window.haklaiApp.showNotification === 'function') {
                window.haklaiApp.showNotification(message, type);
            } else {
                // Fallback: alert simples
                alert(message);
            }
        }
    };
    
    /**
     * Inicialização quando o documento estiver pronto
     */
    $(document).ready(function() {
        // Apenas executa se estiver na página de relatórios
        if ($('.reports-container').length === 0) {
            return;
        }
        
        console.log('Haklai Reports inicializado!');
        
        // Atualiza datas ao mudar período
        $('#reportPeriod').on('change', function() {
            const period = $(this).val();
            const today = new Date();
            let startDate = new Date();
            
            switch(period) {
                case '7':
                    startDate.setDate(today.getDate() - 7);
                    break;
                case '30':
                    startDate.setMonth(today.getMonth() - 1);
                    break;
                case '90':
                    startDate.setMonth(today.getMonth() - 3);
                    break;
                case '365':
                    startDate.setFullYear(today.getFullYear() - 1);
                    break;
                case 'custom':
                    // Usuário define manualmente
                    return;
                default:
                    return;
            }
            
            // Formata data para YYYY-MM-DD
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            $('#reportStartDate').val(formatDate(startDate));
            $('#reportEndDate').val(formatDate(today));
        });
        
        // Destaca filtros quando alterados
        $('#reportStartDate, #reportEndDate, #reportCellFilter').on('change', function() {
            $('#reportPeriod').val('custom');
        });
        
        // Carrega opções quando o nível de filtro muda
        $('#reportFilterLevel').on('change', function() {
            const level = $(this).val();
            HaklaiReports.loadFilterOptions(level);
        });
        
        // Botão de filtrar relatórios
        $('#btnFilterReports').on('click', function() {
            HaklaiReports.filterReports();
        });
        
        // Botão de limpar filtros
        $('#btnResetFilters').on('click', function() {
            HaklaiReports.resetFilters();
        });
        
        // Carrega opções iniciais se houver nível selecionado
        const initialLevel = $('#reportFilterLevel').val();
        if (initialLevel) {
            HaklaiReports.loadFilterOptions(initialLevel);
        }
    });
    
})(jQuery);


