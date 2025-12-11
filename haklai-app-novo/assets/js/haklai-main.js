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
     * Gerenciador de Tema (Claro/Escuro)
     */
    class HaklaiTheme {
        constructor() {
            this.storageKey = 'haklai-theme-mode';
            this.ajax = window.haklai_ajax || {};
            this.root = document.documentElement;
            this.toggleInputs = [];
            this.labelElements = [];
            this.persistTimer = null;
            this.currentMode = 'light';

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initialize());
            } else {
                this.initialize();
            }
        }

        initialize() {
            this.currentMode = this.getInitialTheme();
            this.refreshControls();
            this.applyTheme(this.currentMode, false);
        }

        /**
         * Atualiza referências dos controles e sincroniza estado
         */
        refreshControls() {
            this.cacheElements();
            this.bindToggleEvents();
            this.syncControls();
        }

        cacheElements() {
            this.toggleInputs = Array.from(document.querySelectorAll('[data-haklai-theme-toggle]'));
            this.labelElements = Array.from(document.querySelectorAll('[data-haklai-theme-label]'));
        }

        bindToggleEvents() {
            this.toggleInputs.forEach((input) => {
                if (input.dataset.haklaiThemeBound === 'true') {
                    return;
                }
                input.dataset.haklaiThemeBound = 'true';
                input.addEventListener('change', (event) => {
                    const mode = event.target.checked ? 'dark' : 'light';
                    this.applyTheme(mode, true);
                });
            });
        }

        getInitialTheme() {
            let stored = null;
            try {
                if (window.localStorage) {
                    stored = localStorage.getItem(this.storageKey);
                }
            } catch (error) {
                console.warn('HaklaiTheme: não foi possível acessar o localStorage.', error);
            }

            if (stored === 'dark' || stored === 'light') {
                return stored;
            }

            if (this.root.classList.contains('haklai-theme-dark')) {
                return 'dark';
            }

            if (this.ajax && this.ajax.theme_mode) {
                return this.ajax.theme_mode;
            }

            return 'light';
        }

        applyTheme(mode, persist = true) {
            if (mode !== 'dark' && mode !== 'light') {
                mode = 'light';
            }

            if (mode === 'dark') {
                this.root.classList.add('haklai-theme-dark');
            } else {
                this.root.classList.remove('haklai-theme-dark');
            }

            this.currentMode = mode;
            this.syncControls();

            if (persist) {
                this.persistLocally(mode);
                this.persistRemotely(mode);
            }
        }

        syncControls() {
            const isDark = this.currentMode === 'dark';

            this.toggleInputs.forEach((input) => {
                input.checked = isDark;
            });

            const translations = (this.ajax && this.ajax.translations) || {};
            const labelText = isDark
                ? (translations.theme_light || 'Tema Claro')
                : (translations.theme_dark || 'Tema Escuro');

            this.labelElements.forEach((label) => {
                label.textContent = labelText;
            });

            const containers = document.querySelectorAll('[data-haklai-theme-container]');
            containers.forEach((container) => {
                container.setAttribute('data-theme-current', this.currentMode);
            });
        }

        persistLocally(mode) {
            try {
                if (window.localStorage) {
                    localStorage.setItem(this.storageKey, mode);
                }
            } catch (error) {
                console.warn('HaklaiTheme: não foi possível salvar a preferência local.', error);
            }
        }

        persistRemotely(mode) {
            if (!this.ajax || !this.ajax.ajax_url || !this.ajax.nonce || typeof $ === 'undefined') {
                return;
            }

            if (this.persistTimer) {
                clearTimeout(this.persistTimer);
            }

            this.persistTimer = setTimeout(() => {
                $.post(this.ajax.ajax_url, {
                    action: 'haklai_set_theme_mode',
                    theme_mode: mode,
                    nonce: this.ajax.nonce
                })
                .done(() => {
                    this.ajax.theme_mode = mode;
                })
                .fail((error) => {
                    console.warn('HaklaiTheme: falha ao atualizar o tema global.', error);
                })
                .always(() => {
                    this.persistTimer = null;
                });
            }, 200);
        }
    }

    const haklaiThemeManager = new HaklaiTheme();
    window.haklaiTheme = haklaiThemeManager;

    /**
     * Classe principal do Haklai App
     */
    class HaklaiApp {
        constructor() {
            this.themeManager = window.haklaiTheme || null;
            this.init();
        }

        /**
         * Inicializa a aplicação
         */
        init() {
            this.bindEvents();
            this.initComponents();
            if (this.themeManager) {
                this.themeManager.refreshControls();
            }
            this.loadInitialData();
        }

        /**
         * Vincula eventos
         */
        bindEvents() {
            // Eventos de presença
            $(document).on('click', '.member-card', (e) => {
                // Ignora cliques em botões e elementos interativos dentro do card
                if ($(e.target).is('button, .btn-edit-member, a, input, select, textarea') || 
                    $(e.target).closest('button, .btn-edit-member, a, input, select, textarea').length > 0) {
                    return;
                }
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
            // Converte para jQuery se necessário (pode receber elemento DOM nativo)
            const $cardElement = $card instanceof jQuery ? $card : $($card);
            const memberId = $cardElement.data('member-id') || $cardElement.attr('data-member-id');
            const isPresent = $cardElement.hasClass('present');
            const newStatus = !isPresent;

            // Mostra loading
            $cardElement.addClass('loading');

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
                        // Atualiza visual - remove ambas as classes e adiciona a correta
                        $cardElement.removeClass('present absent');
                        $cardElement.addClass(newStatus ? 'present' : 'absent');
                        
                        // Atualiza badge de status (agora usando haklai-badge)
                        const $badge = $cardElement.find('.haklai-badge');
                        if ($badge.length) {
                            $badge.removeClass('haklai-badge-success haklai-badge-danger');
                            $badge.addClass(newStatus ? 'haklai-badge-success' : 'haklai-badge-danger');
                            $badge.text(newStatus ? 'Presente' : 'Ausente');
                        }

                        // Atualiza foto
                        const $photo = $cardElement.find('.member-photo');
                        $photo.removeClass('present absent');
                        $photo.addClass(newStatus ? 'present' : 'absent');

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
                    $cardElement.removeClass('loading');
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
            
            // CORREÇÃO: Popular campo oculto com URL da foto atual
            $('#edit_current_photo_url').val(memberData.photo || '');
            
            // Mostra preview da foto atual se existir
            if (memberData.photo) {
                $('#editImagePreviewImg').attr('src', memberData.photo);
                $('#editImagePreview').show();
                $('#editImageControls').hide();
            } else {
                $('#editImagePreview').hide();
                $('#editImageControls').show();
            }
            
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
         * Salva edição do membro - VERSÃO COMPLETA COM LOGS
         */
        saveMemberEdit() {
            const $form = $('#editMemberForm');
            const formData = new FormData($form[0]);
            
            // Log dos dados do formulário
            console.log('=== SALVANDO MEMBRO ===');
            console.log('FormData capturado:');
            for (let pair of formData.entries()) {
                console.log(pair[0] + ': ' + pair[1]);
            }
            
            // CORREÇÃO: Buscar URL do upload, não o arquivo
            // Prioridade: 1) URL de novo upload, 2) URL atual do membro, 3) String vazia (não usar File object)
            const photoInput = $form.find('input[name="photo"]');
            const newUploadUrl = photoInput.data('uploadedUrl');
            const currentPhotoUrl = $('#edit_current_photo_url').val();
            const formPhotoValue = formData.get('photo');
            
            // Se formPhotoValue é um File object, não usar (será {} no JSON)
            let uploadedUrl = '';
            if (newUploadUrl) {
                uploadedUrl = newUploadUrl; // Nova foto enviada
            } else if (currentPhotoUrl) {
                uploadedUrl = currentPhotoUrl; // Foto atual do membro
            } else if (formPhotoValue && typeof formPhotoValue === 'string') {
                uploadedUrl = formPhotoValue; // URL string (não File object)
            }
            
            console.log('Foto capturada:', {
                newUploadUrl: newUploadUrl || 'N/A',
                currentPhotoUrl: currentPhotoUrl || 'N/A',
                formPhotoValue: formPhotoValue instanceof File ? '[File object]' : formPhotoValue || 'N/A',
                finalUrl: uploadedUrl || 'VAZIO'
            });
            
            // Captura Encontro com Deus
            const encontroDeus = $('#edit_encontro_deus').is(':checked') ? 'sim' : 'nao';
            console.log('Encontro com Deus:', encontroDeus);
            
            // Captura Formações
            const formacoes = $('input[name="formacoes[]"]:checked').map(function() {
                return $(this).val();
            }).get();
            console.log('Formações:', formacoes);
            
            const data = {
                action_type: 'edit_member',
                data: {
                    member_id: formData.get('member_id'),
                    name: formData.get('name'),
                    email: formData.get('email'),
                    phone: formData.get('phone'),
                    photo: uploadedUrl,
                    skill: formData.get('skill'),
                    baptism_status: formData.get('baptism_status'),
                    baptism_date: formData.get('baptism_date'),
                    status: formData.get('status'),
                    encontro_deus: encontroDeus,
                    formacoes: formacoes
                }
            };
            
            console.log('Dados finais para envio:', JSON.stringify(data, null, 2));
            console.log('========================');
            
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
                        console.error('Resposta do servidor indicou erro:', response);
                        this.showNotification(response.message || 'Erro ao atualizar membro', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Erro ao atualizar membro:', error);
                    this.showNotification('Erro de conexão. Verifique o console para mais detalhes.', 'error');
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
            // FASE 1: Não executa na página de relatórios
            // Verifica se está na página de relatórios (tem .reports-container)
            if ($('.reports-container').length > 0) {
                // NÃO executa na página de relatórios - valores vêm do PHP
                return;
            }
            
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
         * Envia requisição AJAX com logs detalhados
         */
        sendAjaxRequest(data) {
            return new Promise((resolve, reject) => {
                // Log detalhado antes de enviar
                console.log('=== AJAX REQUEST INICIADO ===');
                console.log('Action Type:', data.action_type);
                console.log('URL:', window.haklaiCheckpoint.ajaxUrl);
                console.log('Nonce:', window.haklaiCheckpoint.nonce ? 'PRESENTE' : 'AUSENTE');
                console.log('Dados Enviados:', JSON.stringify(data.data, null, 2));
                console.log('============================');
                
                $.ajax({
                    url: window.haklaiCheckpoint.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'haklai_ajax_handler',
                        action_type: data.action_type,
                        data: data.data,
                        nonce: window.haklaiCheckpoint.nonce
                    },
                    timeout: 30000, // 30 segundos de timeout
                    success: (response) => {
                        console.log('=== AJAX SUCCESS ===');
                        console.log('Status: OK');
                        console.log('Response Completa:', response);
                        console.log('Success:', response.success);
                        console.log('Message:', response.message || 'N/A');
                        console.log('Data:', response.data || 'N/A');
                        console.log('====================');
                        
                        // Verifica se a resposta é válida
                        if (!response || typeof response !== 'object') {
                            console.error('Resposta inválida do servidor:', response);
                            reject('Resposta inválida do servidor');
                            return;
                        }
                        
                        resolve(response);
                    },
                    error: (xhr, status, error) => {
                        console.error('=== AJAX ERROR ===');
                        console.error('Status HTTP:', xhr.status);
                        console.error('Status Text:', xhr.statusText);
                        console.error('Error:', error);
                        console.error('Response Text:', xhr.responseText);
                        console.error('URL Chamada:', window.haklaiCheckpoint.ajaxUrl);
                        console.error('Action:', 'haklai_ajax_handler');
                        console.error('Action Type:', data.action_type);
                        console.error('Dados Enviados:', JSON.stringify(data.data, null, 2));
                        
                        // Tenta parsear resposta se for JSON
                        let errorMessage = 'Erro de conexão';
                        if (xhr.responseText) {
                            try {
                                const errorResponse = JSON.parse(xhr.responseText);
                                errorMessage = errorResponse.message || errorResponse.data?.message || xhr.responseText;
                            } catch (e) {
                                errorMessage = xhr.responseText;
                            }
                        }
                        
                        console.error('Mensagem de Erro:', errorMessage);
                        console.error('Headers:', xhr.getAllResponseHeaders());
                        console.error('==================');
                        reject(errorMessage || xhr.responseText || error || status);
                    },
                    complete: (xhr, status) => {
                        console.log('=== AJAX COMPLETE ===');
                        console.log('Status Final:', status);
                        console.log('====================');
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
         * Upload de imagem SIMPLIFICADO (sem compressão frontend)
         */
        uploadImage(file, callback) {
            // Valida tamanho do arquivo
            if (!this.validateImageSize(file)) {
                return;
            }

            // Mostra progresso
            this.showUploadProgress();

            // Upload direto sem compressão
            this.uploadDirectImage(file, callback);
        }

        /**
         * Valida tamanho da imagem
         */
        validateImageSize(file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                this.showNotification('Imagem muito grande. Máximo 10MB.', 'warning');
                return false;
            }
            return true;
        }

        /**
         * Upload direto da imagem (sem compressão)
         */
        uploadDirectImage(file, callback) {
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
                xhr: () => {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            const percentComplete = (e.loaded / e.total) * 100;
                            this.updateUploadProgress(percentComplete);
                        }
                    });
                    return xhr;
                },
                success: (response) => {
                    this.hideUploadProgress();
                    if (response.success) {
                        callback(response.data.url);
                        this.showNotification('Imagem enviada com sucesso!', 'success');
                    } else {
                        this.showNotification(response.data?.message || 'Erro ao fazer upload da imagem', 'error');
                    }
                },
                error: (xhr) => {
                    this.hideUploadProgress();
                    console.error('Erro de upload:', xhr);
                    this.showNotification('Erro de conexão ao fazer upload', 'error');
                }
            });
        }

        /**
         * Mostra progresso do upload
         */
        showUploadProgress() {
            if ($('.haklai-upload-progress').length === 0) {
                $('body').append(`
                    <div class="haklai-upload-progress">
                        <div class="upload-progress-container">
                            <div class="upload-progress-bar">
                                <div class="upload-progress-fill"></div>
                            </div>
                            <div class="upload-progress-text">Preparando upload...</div>
                        </div>
                    </div>
                `);
            }
        }

        /**
         * Atualiza progresso do upload
         */
        updateUploadProgress(percentage) {
            $('.upload-progress-fill').css('width', percentage + '%');
            $('.upload-progress-text').text(`Enviando... ${Math.round(percentage)}%`);
        }

        /**
         * Esconde progresso do upload
         */
        hideUploadProgress() {
            $('.haklai-upload-progress').fadeOut(300, function() {
                $(this).remove();
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
        toggleTheme: function() {
            if (window.haklaiTheme) {
                const nextMode = window.haklaiTheme.currentMode === 'dark' ? 'light' : 'dark';
                window.haklaiTheme.applyTheme(nextMode, true);
            }
        }
    };

    /**
     * Inicialização quando o documento estiver pronto
     */
    $(document).ready(function() {
        // Inicializa aplicação principal
        window.haklaiApp = new HaklaiApp();
        
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
        
        // Upload de imagens SIMPLIFICADO
        $('input[type="file"]').on('change', function() {
            const file = this.files[0];
            const $input = $(this);
            
            if (file && file.type.startsWith('image/')) {
                // Validação básica
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('Imagem muito grande. Máximo 10MB.');
                    this.value = '';
                    return;
                }
                
                // Upload direto
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

