/**
 * JavaScript para página de Gerenciamento de Tenants
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

(function($) {
    'use strict';

    /**
     * Mostra mensagem
     */
    function showMessage(container, message, type) {
        container.removeClass('success error')
                 .addClass(type)
                 .text(message)
                 .slideDown();
        
        // Auto-hide após 5 segundos
        setTimeout(function() {
            container.slideUp();
        }, 5000);
    }

    /**
     * Esconde mensagem
     */
    function hideMessage(container) {
        container.slideUp();
    }

    /**
     * Define estado de loading
     */
    function setLoading(button, loading) {
        if (loading) {
            button.prop('disabled', true)
                  .data('original-text', button.text())
                  .text(haklaiTenants.i18n.loading);
        } else {
            button.prop('disabled', false)
                  .text(button.data('original-text') || button.text());
        }
    }

    /**
     * Abre modal de associação
     */
    function openAssociateModal(userId, userName, tenantId, tenantName) {
        $('#associate_user_id').val(userId);
        $('#associate_tenant_id').val(tenantId);
        var message = haklaiTenants.i18n.confirm_associate
            .replace('{user}', userName || 'este usuário')
            .replace('{tenant}', tenantName || 'este tenant');
        $('#haklai-modal-message').text(message);
        $('#haklai-associate-modal').fadeIn();
    }

    /**
     * Fecha modal
     */
    function closeModal() {
        $('#haklai-associate-modal').fadeOut();
        $('#haklai-associate-message').hide();
    }

    // Criar Tenant
    $('#haklai-create-tenant-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $form.find('button[type="submit"]');
        var $message = $('#haklai-create-message');
        
        hideMessage($message);
        
        if (!confirm(haklaiTenants.i18n.confirm_create)) {
            return;
        }
        
        setLoading($button, true);
        
        $.ajax({
            url: haklaiTenants.ajax_url,
            type: 'POST',
            data: {
                action: 'haklai_create_tenant_admin',
                nonce: haklaiTenants.nonce,
                church_name: $('#create_church_name').val(),
                location: $('#create_location').val(),
                contact: $('#create_contact').val(),
                supervisor_user_id: $('#create_supervisor_user_id').val()
            },
            success: function(response) {
                setLoading($button, false);
                
                if (response.success) {
                    showMessage($message, response.data.message, 'success');
                    $form[0].reset();
                    // Recarrega página após 2 segundos
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showMessage($message, response.data.message || haklaiTenants.i18n.error, 'error');
                }
            },
            error: function() {
                setLoading($button, false);
                showMessage($message, haklaiTenants.i18n.error, 'error');
            }
        });
    });

    // Associar Usuário (botão na lista de tenants)
    $('.haklai-associate-btn').on('click', function() {
        var $btn = $(this);
        var tenantId = $btn.data('tenant-id');
        var tenantName = $btn.data('tenant-name');
        
        // Abre modal para selecionar usuário
        // Por enquanto, vamos usar o select da lista de usuários sem tenant
        alert('Selecione um usuário da lista "Usuários Sem Tenant" e escolha o tenant "' + tenantName + '" no dropdown.');
    });

    // Selecionar tenant para associar usuário
    $('.haklai-associate-select').on('change', function() {
        var $select = $(this);
        var $btn = $select.siblings('.haklai-associate-user-btn');
        
        if ($select.val() != '0') {
            $btn.show();
        } else {
            $btn.hide();
        }
    });

    // Associar Usuário (botão na lista de usuários sem tenant)
    $('.haklai-associate-user-btn').on('click', function() {
        var $btn = $(this);
        var userId = $btn.data('user-id');
        var userName = $btn.data('user-name');
        var $select = $btn.siblings('.haklai-associate-select');
        var tenantId = $select.val();
        
        if (!tenantId || tenantId == '0') {
            alert('Por favor, selecione um tenant primeiro.');
            return;
        }
        
        // Busca nome do tenant
        var tenantName = $select.find('option:selected').text();
        
        openAssociateModal(userId, userName, tenantId, tenantName);
    });

    // Confirmar associação (modal)
    $('#haklai-associate-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $button = $form.find('button[type="submit"]');
        var $message = $('#haklai-associate-message');
        
        hideMessage($message);
        setLoading($button, true);
        
        $.ajax({
            url: haklaiTenants.ajax_url,
            type: 'POST',
            data: {
                action: 'haklai_associate_user_to_tenant',
                nonce: haklaiTenants.nonce,
                user_id: $('#associate_user_id').val(),
                tenant_id: $('#associate_tenant_id').val()
            },
            success: function(response) {
                setLoading($button, false);
                
                if (response.success) {
                    showMessage($message, response.data.message, 'success');
                    // Fecha modal e recarrega página após 2 segundos
                    setTimeout(function() {
                        closeModal();
                        location.reload();
                    }, 2000);
                } else {
                    showMessage($message, response.data.message || haklaiTenants.i18n.error, 'error');
                }
            },
            error: function() {
                setLoading($button, false);
                showMessage($message, haklaiTenants.i18n.error, 'error');
            }
        });
    });

    // Fechar modal
    $('.haklai-modal-close, .haklai-modal-cancel').on('click', function() {
        closeModal();
    });

    // Fechar modal ao clicar fora
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('haklai-modal')) {
            closeModal();
        }
    });

})(jQuery);

