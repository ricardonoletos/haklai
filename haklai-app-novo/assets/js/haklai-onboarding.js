/**
 * JavaScript da página de onboarding do Haklai
 * FASE 5: Fluxo de registro inicial para Tenant Owner
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const $form = $('#haklai-onboarding-form');
        const $submitButton = $('#haklai-onboarding-submit');
        const $buttonText = $submitButton.find('.haklai-button-text');
        const $buttonSpinner = $submitButton.find('.haklai-button-spinner');
        const $messages = $('#haklai-onboarding-messages');

        /**
         * Exibe mensagem de feedback
         */
        function showMessage(message, type) {
            $messages
                .removeClass('haklai-message-success haklai-message-error haklai-message-info')
                .addClass('haklai-message-' + type)
                .text(message)
                .slideDown(200);
        }

        /**
         * Esconde mensagem de feedback
         */
        function hideMessage() {
            $messages.slideUp(200);
        }

        /**
         * Ativa estado de loading
         */
        function setLoading(loading) {
            if (loading) {
                $submitButton.prop('disabled', true);
                $buttonText.text(haklaiOnboarding.i18n.loading);
                $buttonSpinner.show();
                    } else {
                        $submitButton.prop('disabled', false);
                        $buttonText.text('Avançar'); // Texto padrão do botão
                        $buttonSpinner.hide();
                    }
        }

        /**
         * Valida formulário antes de enviar
         */
        function validateForm() {
            const supervisorName = $('#supervisor_name').val().trim();
            const churchName = $('#church_name').val().trim();
            const location = $('#location').val().trim();
            const contact = $('#contact').val().trim();

            if (!supervisorName || !churchName || !location || !contact) {
                showMessage(haklaiOnboarding.i18n.required_fields, 'error');
                return false;
            }

            return true;
        }

        /**
         * Submete formulário via AJAX
         */
        function submitForm() {
            // Valida antes de enviar
            if (!validateForm()) {
                return;
            }

            // Esconde mensagens anteriores
            hideMessage();

            // Ativa loading
            setLoading(true);

            // Prepara dados
            const formData = {
                action: 'haklai_register_tenant',
                nonce: haklaiOnboarding.nonce,
                supervisor_name: $('#supervisor_name').val().trim(),
                church_name: $('#church_name').val().trim(),
                location: $('#location').val().trim(),
                contact: $('#contact').val().trim()
            };

            // Envia via AJAX
            $.ajax({
                url: haklaiOnboarding.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    setLoading(false);

                    if (response.success) {
                        // Sucesso
                        showMessage(response.data.message || haklaiOnboarding.i18n.success, 'success');

                        // Redireciona após 1 segundo
                        setTimeout(function() {
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            } else {
                                window.location.href = haklaiOnboarding.ajax_url.replace('admin-ajax.php', 'admin.php?page=haklai');
                            }
                        }, 1000);
                    } else {
                        // Erro
                        const errorMessage = response.data && response.data.message 
                            ? response.data.message 
                            : haklaiOnboarding.i18n.error;
                        showMessage(errorMessage, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    setLoading(false);
                    console.error('Erro AJAX:', error);
                    showMessage(haklaiOnboarding.i18n.error, 'error');
                }
            });
        }

        /**
         * Handler de submissão do formulário
         */
        $form.on('submit', function(e) {
            e.preventDefault();
            submitForm();
        });

        /**
         * Validação em tempo real dos campos
         */
        $form.find('input[required]').on('blur', function() {
            const $field = $(this);
            const value = $field.val().trim();

            if (!value) {
                $field.addClass('haklai-field-error');
            } else {
                $field.removeClass('haklai-field-error');
            }
        });

        /**
         * Limpa mensagens ao digitar
         */
        $form.find('input').on('input', function() {
            if ($messages.is(':visible')) {
                hideMessage();
            }
        });
    });
})(jQuery);

