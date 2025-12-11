/**
 * Haklai App - JavaScript Principal
 * Baseado exatamente na funcionalidade do test-checkpoint-final.html
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

(function($) {
    'use strict';

    /**
     * Classe principal do Haklai App
     */
    class HaklaiApp {
        constructor() {
            this.init();
        }

        /**
         * Inicializa a aplicação
         */
        init() {
            this.bindEvents();
            this.initComponents();
            this.loadInitialData();
        }

        /**
         * Vincula eventos
         */
        bindEvents() {
            // Eventos de presença
            $(document).on('click', '.member-card', (e) => {
                this.togglePresence($(e.currentTarget));
            });

            // Eventos de formulários
            $(document).on('submit', '#addVisitorForm', (e) => {
                e.preventDefault();
                this.addVisitor();
            });

            $(document).on('submit', '#addMemberForm', (e) => {
                e.preventDefault();
                this.addMember();
            });

            // Eventos de busca
            $(document).on('input', '#anfitriaoSearch', (e) => {
                this.searchMembers($(e.target).val());
            });

            // Eventos de modal
            $(document).on('hidden.bs.modal', '.modal', () => {
                this.clearForms();
            });

            // Eventos de notificação
            $(document).on('click', '.haklai-notification', (e) => {
                $(e.currentTarget).remove();
            });

            // Eventos de teclado
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeModals();
                }
            });
        }

        /**
         * Inicializa componentes
         */
        initComponents() {
            // Inicializa tooltips
            if (typeof bootstrap !== 'undefined') {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            // Inicializa date picker
            this.initDatePicker();

            // Inicializa busca de anfitrião
            this.initAnfitriaoSearch();

            // Configura auto-save
            this.initAutoSave();
        }

        /**
         * Carrega dados iniciais
         */
        loadInitialData() {
            // Atualiza estatísticas iniciais
            this.updateStats();

            // Carrega histórico de presenças se disponível
            if (window.haklaiCheckpoint && window.haklaiCheckpoint.cellId) {
                this.loadAttendanceHistory();
            }
        }

        /**
         * Alterna presença de membro
         */
        togglePresence($card) {
            const memberId = $card.data('member-id');
            const isPresent = $card.hasClass('present');
            const newStatus = !isPresent;

            // Mostra loading
            $card.addClass('loading');

            // Dados para envio
            const data = {
                action_type: 'toggle_presence',
                data: {
                    member_id: memberId,
                    meeting_id: window.haklaiCheckpoint.meetingId,
                    is_present: newStatus
                }
            };

            // Envia requisição AJAX
            this.sendAjaxRequest(data)
                .then((response) => {
                    if (response.success) {
                        // Atualiza visual
                        $card.toggleClass('present absent');
                        
                        // Atualiza badge de status
                        const $badge = $card.find('.status-badge');
                        $badge.removeClass('status-present status-absent');
                        $badge.addClass(newStatus ? 'status-present' : 'status-absent');
                        $badge.find('span').text(newStatus ? 'Presente' : 'Ausente');

                        // Atualiza foto
                        const $photo = $card.find('.member-photo');
                        $photo.toggleClass('present absent', newStatus);

                        // Atualiza estatísticas
                        this.updateStats();

                        // Mostra notificação
                        this.showNotification(
                            newStatus ? 'Membro marcado como presente' : 'Membro marcado como ausente',
                            'success'
                        );
                    } else {
                        this.showNotification(response.message || 'Erro ao atualizar presença', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Erro ao atualizar presença:', error);
                    this.showNotification('Erro de conexão. Tente novamente.', 'error');
                })
                .finally(() => {
                    // Remove loading
                    $card.removeClass('loading');
                });
        }

        /**
         * Adiciona visitante
         */
        addVisitor() {
            const $form = $('#addVisitorForm');
            const formData = new FormData($form[0]);
            const photoInput = $form.find('input[name="photo"]');
            const uploadedUrl = photoInput.data('uploadedUrl') || '';
            const data = {
                action_type: 'add_visitor',
                data: {
                    name: formData.get('name'),
                    phone: formData.get('phone'),
                    photo: uploadedUrl,
                    is_visitor: true,
                    baptism_status: 'visitor',
                    cell_id: window.haklaiCheckpoint.cellId,
                    role_level: 0
                }
            };

            this.sendAjaxRequest(data)
                .then((response) => {
                    if (response.success) {
                        this.showNotification('Visitante adicionado com sucesso!', 'success');
                        $('#addVisitorModal').modal('hide');
                        this.refreshMembers();
                    } else {
                        this.showNotification(response.message || 'Erro ao adicionar visitante', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Erro ao adicionar visitante:', error);
                    this.showNotification('Erro de conexão. Tente novamente.', 'error');
                });
        }

        /**
         * Adiciona membro
         */
        addMember() {
            const $form = $('#addMemberForm');
            const formData = new FormData($form[0]);
            const photoInput = $form.find('input[name="photo"]');
            const uploadedUrl = photoInput.data('uploadedUrl') || '';
            const data = {
                action_type: 'add_member',
                data: {
                    name: formData.get('name'),
                    phone: formData.get('phone'),
                    email: formData.get('email'),
                    photo: uploadedUrl,
                    skill: formData.get('skill'),
                    leader_id: formData.get('leader_id'),
                    baptism_status: formData.get('baptism_status'),
                    encontro_deus: $('#add_encontro_deus').is(':checked') ? 'sim' : 'nao',
                    formacoes: $('input[name="formacoes[]"]:checked').map(function() {
                        return $(this).val();
                    }).get(),
                    cell_id: window.haklaiCheckpoint.cellId,
                    role_level: 0,
                    is_visitor: false
                }
            };

            this.sendAjaxRequest(data)
                .then((response) => {
                    if (response.success) {
                        this.showNotification('Membro registrado com sucesso!', 'success');
                        $('#addMemberModal').modal('hide');
                        this.refreshMembers();
                    } else {
                        this.showNotification(response.message || 'Erro ao registrar membro', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Erro ao registrar membro:', error);
                    this.showNotification('Erro de conexão. Tente novamente.', 'error');
                });
        }

        /**
         * Envia relatório
         */
        sendReport() {
            const data = {
                action_type: 'send_report',
                data: {
                    meeting_id: window.haklaiCheckpoint.meetingId,
                    cell_id: window.haklaiCheckpoint.cellId
                }
            };

            this.sendAjaxRequest(data)
                .then((response) => {
                    if (response.success) {
                        this.showNotification('Relatório enviado com sucesso!', 'success');
                    } else {
                        this.showNotification(response.message || 'Erro ao enviar relatório', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Erro ao enviar relatório:', error);
                    this.showNotification('Erro de conexão. Tente novamente.', 'error');
                });
        }

        /**
         * Abre modal de edição de membro
         */
        editMember(memberId) {
            const data = {
                action_type: 'get_member_data',
                data: {
                    member_id: memberId
                }
            };

            this.sendAjaxRequest(data)
                .then((response) => {
                    if (response.success && response.data) {
                        this.populateEditForm(response.data);
                        $('#editMemberModal').modal('show');
                    } else {
                        this.showNotification(response.message || 'Erro ao carregar dados do membro', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Erro ao carregar membro:', error);
                    this.showNotification('Erro de conexão. Tente novamente.', 'error');
                });
        }

        /**
         * Popula formulário de edição com dados do membro
         */
        populateEditForm(memberData) {
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
            
            // Esconde alerta inicialmente
            $('#baptism_change_alert').hide();
        }

        /**
         * Salva edição do membro
         */
        saveMemberEdit() {
            const $form = $('#editMemberForm');
            const formData = new FormData($form[0]);
            
            const data = {
                action_type: 'edit_member',
                data: {
                    member_id: formData.get('member_id'),
                    name: formData.get('name'),
                    email: formData.get('email'),
                    phone: formData.get('phone'),
                    photo: formData.get('photo'),
                    skill: formData.get('skill'),
                    baptism_status: formData.get('baptism_status'),
                    baptism_date: formData.get('baptism_date'),
                    status: formData.get('status'),
                    encontro_deus: $('#edit_encontro_deus').is(':checked') ? 'sim' : 'nao',
                    formacoes: $('input[name="formacoes[]"]:checked').map(function() {
                        return $(this).val();
                    }).get()
                }
            };

            this.sendAjaxRequest(data)
                .then((response) => {
                    if (response.success) {
                        this.showNotification('Membro atualizado com sucesso!', 'success');
                        $('#editMemberModal').modal('hide');
                        
                        // Recarrega a página após 1 segundo
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        this.showNotification(response.message || 'Erro ao atualizar membro', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Erro ao atualizar membro:', error);
                    this.showNotification('Erro de conexão. Tente novamente.', 'error');
                });
        }

        /**
         * Busca membros para anfitrião
         */
        searchMembers(searchTerm) {
            if (searchTerm.length < 2) {
                $('#searchResults').hide();
                return;
            }

            const members = window.haklaiCheckpoint.members.filter(member => 
                member.name.toLowerCase().includes(searchTerm.toLowerCase())
            );

            if (members.length > 0) {
                let html = '';
                members.forEach(member => {
                    html += `<div class="search-result-item" onclick="HaklaiApp.selectAnfitriao('${member.name}')">${member.name}</div>`;
                });
                $('#searchResults').html(html).show();
            } else {
                $('#searchResults').hide();
            }
        }

        /**
         * Seleciona anfitrião
         */
        selectAnfitriao(name) {
            $('#anfitriaoSearch').val(name);
            $('#searchResults').hide();
        }

        /**
         * Atualiza estatísticas
         */
        updateStats() {
            const $cards = $('.member-card');
            const $presentCards = $('.member-card.present');
            
            const totalMembers = $cards.length;
            const presentMembers = $presentCards.length;
            
            // Conta Frequentadores Assíduos (F.A)
            let frequentVisitors = 0;
            $cards.each(function() {
                const $card = $(this);
                // Verifica se o membro tem o atributo data-baptism-status
                if ($card.data('baptism-status') === 'frequent_visitor') {
                    frequentVisitors++;
                }
            });
            
            // Na Última Célula = Presentes na reunião atual
            const lastMeetingPresent = presentMembers;
            
            const presenceRate = totalMembers > 0 ? Math.round((presentMembers / totalMembers) * 100 * 10) / 10 : 0;

            // Atualiza números
            $('.stat-card:nth-child(1) .number').text(totalMembers);
            $('.stat-card:nth-child(2) .number').text(frequentVisitors);
            $('.stat-card:nth-child(3) .number').text(lastMeetingPresent);
            $('.stat-card:nth-child(4) .number').text(presenceRate + '%');

            // Anima mudanças
            this.animateStatChange();
        }

        /**
         * Anima mudança de estatísticas
         */
        animateStatChange() {
            $('.stat-card .number').each(function() {
                const $this = $(this);
                $this.addClass('pulse');
                setTimeout(() => {
                    $this.removeClass('pulse');
                }, 500);
            });
        }

        /**
         * Carrega histórico de presenças
         */
        loadAttendanceHistory() {
            const data = {
                action_type: 'get_attendance_history',
                data: {
                    cell_id: window.haklaiCheckpoint.cellId
                }
            };

            this.sendAjaxRequest(data)
                .then((response) => {
                    if (response.success) {
                        this.displayAttendanceHistory(response.data);
                    }
                })
                .catch((error) => {
                    console.error('Erro ao carregar histórico:', error);
                });
        }

        /**
         * Exibe histórico de presenças
         */
        displayAttendanceHistory(history) {
            // Implementar exibição do histórico
            console.log('Histórico de presenças:', history);
        }

        /**
         * Atualiza lista de membros
         */
        refreshMembers() {
            // Recarrega a página ou atualiza via AJAX
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }

        /**
         * Inicializa date picker
         */
        initDatePicker() {
            const $dateInput = $('#meetingDate');
            if ($dateInput.length) {
                $dateInput.on('change', () => {
                    this.updateMeetingDate($dateInput.val());
                });
            }
        }

        /**
         * Atualiza data da reunião
         */
        updateMeetingDate(date) {
            // Implementar atualização da data
            console.log('Data da reunião atualizada para:', date);
        }

        /**
         * Inicializa busca de anfitrião
         */
        initAnfitriaoSearch() {
            const $searchInput = $('#anfitriaoSearch');
            if ($searchInput.length) {
                // Implementar funcionalidade de busca
            }
        }

        /**
         * Inicializa auto-save
         */
        initAutoSave() {
            // Auto-save a cada 30 segundos
            setInterval(() => {
                this.autoSave();
            }, 30000);
        }

        /**
         * Auto-save
         */
        autoSave() {
            // Implementar auto-save
            console.log('Auto-save executado');
        }

        /**
         * Limpa formulários
         */
        clearForms() {
            $('#addVisitorForm')[0].reset();
            $('#addMemberForm')[0].reset();
        }

        /**
         * Fecha modais
         */
        closeModals() {
            $('.modal').modal('hide');
        }

        /**
         * Mostra notificação
         */
        showNotification(message, type = 'info') {
            const $notification = $(`
                <div class="haklai-notification ${type}">
                    <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                    ${message}
                </div>
            `);

            $('body').append($notification);

            // Mostra notificação
            setTimeout(() => {
                $notification.addClass('show');
            }, 100);

            // Remove após 5 segundos
            setTimeout(() => {
                $notification.removeClass('show');
                setTimeout(() => {
                    $notification.remove();
                }, 300);
            }, 5000);
        }

        /**
         * Obtém ícone da notificação
         */
        getNotificationIcon(type) {
            const icons = {
                success: 'check-circle',
                error: 'exclamation-circle',
                warning: 'exclamation-triangle',
                info: 'info-circle'
            };
            return icons[type] || 'info-circle';
        }

        /**
         * Envia requisição AJAX
         */
        sendAjaxRequest(data) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: window.haklaiCheckpoint.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'haklai_ajax_handler',
                        action_type: data.action_type,
                        data: data.data,
                        nonce: window.haklaiCheckpoint.nonce
                    },
                    success: (response) => {
                        console.log('AJAX Success:', response);
                        resolve(response);
                    },
                    error: (xhr, status, error) => {
                        console.error('AJAX Error Details:', {
                            status: xhr.status,
                            statusText: status,
                            error: error,
                            responseText: xhr.responseText,
                            url: window.haklaiCheckpoint.ajaxUrl,
                            action: 'haklai_ajax_handler',
                            action_type: data.action_type,
                            sentData: data
                        });
                        reject(xhr.responseText || error || status);
                    }
                });
            });
        }

        /**
         * Valida formulário
         */
        validateForm($form) {
            let isValid = true;
            const requiredFields = $form.find('[required]');

            requiredFields.each(function() {
                const $field = $(this);
                if (!$field.val().trim()) {
                    $field.addClass('is-invalid');
                    isValid = false;
                } else {
                    $field.removeClass('is-invalid');
                }
            });

            return isValid;
        }

        /**
         * Formata telefone
         */
        formatPhone(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length >= 11) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length >= 7) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
            }
            input.value = value;
        }

        /**
         * Upload de imagem
         */
        uploadImage(file, callback) {
            const formData = new FormData();
            formData.append('image', file);
            formData.append('action', 'haklai_upload_image');
            formData.append('nonce', window.haklaiCheckpoint.nonce);

            $.ajax({
                url: window.haklaiCheckpoint.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        callback(response.data.url);
                    } else {
                        this.showNotification('Erro ao fazer upload da imagem', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Erro de conexão ao fazer upload', 'error');
                }
            });
        }

        /**
         * Exporta dados
         */
        exportData(format = 'csv') {
            const data = {
                action_type: 'export_data',
                data: {
                    format: format,
                    cell_id: window.haklaiCheckpoint.cellId,
                    meeting_id: window.haklaiCheckpoint.meetingId
                }
            };

            this.sendAjaxRequest(data)
                .then((response) => {
                    if (response.success) {
                        // Cria link de download
                        const link = document.createElement('a');
                        link.href = response.data.url;
                        link.download = response.data.filename;
                        link.click();
                        
                        this.showNotification('Dados exportados com sucesso!', 'success');
                    } else {
                        this.showNotification('Erro ao exportar dados', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Erro ao exportar dados:', error);
                    this.showNotification('Erro de conexão ao exportar', 'error');
                });
        }

        /**
         * Imprime página
         */
        printPage() {
            window.print();
        }

        /**
         * Alterna tema
         */
        toggleTheme() {
            const currentTheme = document.body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.body.setAttribute('data-theme', newTheme);
            localStorage.setItem('haklai-theme', newTheme);
            
            this.showNotification(`Tema alterado para ${newTheme}`, 'info');
        }

        /**
         * Carrega tema salvo
         */
        loadSavedTheme() {
            const savedTheme = localStorage.getItem('haklai-theme');
            if (savedTheme) {
                document.body.setAttribute('data-theme', savedTheme);
            }
        }
    }

    /**
     * Funções globais para compatibilidade com HTML original
     */
    function _callIfReady(method, args){
        if (window.haklaiApp && typeof window.haklaiApp[method] === 'function') {
            return window.haklaiApp[method].apply(window.haklaiApp, args || []);
        }
    }

    window.HaklaiApp = {
        togglePresence: function(card) { _callIfReady('togglePresence', [$(card)]); },
        addMember: function() { _callIfReady('addMember'); },
        addVisitor: function() { _callIfReady('addVisitor'); },
        sendReport: function() { _callIfReady('sendReport'); },
        editMember: function(memberId) { _callIfReady('editMember', [memberId]); },
        saveMemberEdit: function() { _callIfReady('saveMemberEdit'); },
        selectAnfitriao: function(name) { _callIfReady('selectAnfitriao', [name]); },
        updateStats: function() { _callIfReady('updateStats'); },
        searchMembers: function(term) { _callIfReady('searchMembers', [term]); },
        exportData: function(format) { _callIfReady('exportData', [format]); },
        printPage: function() { _callIfReady('printPage'); },
        toggleTheme: function() { _callIfReady('toggleTheme'); }
    };

    /**
     * Inicialização quando o documento estiver pronto
     */
    $(document).ready(function() {
        // Inicializa aplicação principal
        window.haklaiApp = new HaklaiApp();
        
        // Carrega tema salvo
        window.haklaiApp.loadSavedTheme();
        
        // Formatação automática de telefone
        $('input[type="tel"]').on('input', function() {
            window.haklaiApp.formatPhone(this);
        });
        
        // Validação de formulários
        $('form').on('submit', function(e) {
            if (!window.haklaiApp.validateForm($(this))) {
                e.preventDefault();
                window.haklaiApp.showNotification('Por favor, preencha todos os campos obrigatórios', 'warning');
            }
        });
        
        // Upload de imagens
        $('input[type="file"]').on('change', function() {
            const file = this.files[0];
            const $input = $(this);
            if (file && file.type.startsWith('image/')) {
                window.haklaiApp.uploadImage(file, (url) => {
                    // Guarda URL no input (para uso nos submits)
                    $input.data('uploadedUrl', url);
                    // Atualiza preview da imagem, se existir
                    const $preview = $input.siblings('.image-preview');
                    if ($preview.length) {
                        $preview.attr('src', url).show();
                    }
                });
            } else {
                // Limpa URL armazenada se não for imagem
                $input.removeData('uploadedUrl');
            }
        });
        
        // Atalhos de teclado
        $(document).on('keydown', function(e) {
            // Ctrl+S para salvar
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                window.haklaiApp.sendReport();
            }
            
            // Ctrl+P para imprimir
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.haklaiApp.printPage();
            }
            
            // F5 para atualizar estatísticas
            if (e.key === 'F5') {
                e.preventDefault();
                window.haklaiApp.updateStats();
            }
        });
        
        console.log('Haklai App inicializado com sucesso!');
    });

    /**
     * CSS para animações
     */
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .pulse {
                animation: pulse 0.5s ease-in-out;
            }
            
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
            
            .is-invalid {
                border-color: var(--danger-color) !important;
                box-shadow: 0 0 0 0.2rem rgba(239, 68, 68, 0.25) !important;
            }
            
            .image-preview {
                max-width: 100px;
                max-height: 100px;
                border-radius: 50%;
                object-fit: cover;
                margin-top: 10px;
                display: none;
            }
            
            [data-theme="dark"] {
                --text-primary: #f7fafc;
                --text-secondary: #e2e8f0;
                --text-muted: #a0aec0;
                --bg-primary: #1a202c;
                --bg-secondary: #2d3748;
                --bg-tertiary: #4a5568;
                --border-color: #4a5568;
            }
        `)
        .appendTo('head');

})(jQuery);

