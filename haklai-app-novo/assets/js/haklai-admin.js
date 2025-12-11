(function($) {
    'use strict';
    
    /**
     * Função global para selecionar imagem usando WordPress Media Library
     * 
     * @param {string} fieldId - ID do campo input onde a URL da imagem será inserida
     */
    window.haklaiSelectImage = function(fieldId) {
        var mediaUploader;
        
        // Se o uploader já existe, reabre
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        // Cria o media uploader do WordPress
        mediaUploader = wp.media({
            title: 'Selecionar Foto',
            button: {
                text: 'Usar esta imagem'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        // Quando uma imagem é selecionada
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            // Insere a URL no campo
            $('#' + fieldId).val(attachment.url);
            
            // Exibe preview se existir elemento de preview
            var $preview = $('#' + fieldId + '_preview');
            if ($preview.length) {
                $preview.attr('src', attachment.url).show();
            } else {
                // Cria preview dinamicamente se não existir
                var previewHtml = '<br><img id="' + fieldId + '_preview" src="' + attachment.url + '" style="max-width: 150px; max-height: 150px; margin-top: 10px; border: 1px solid #ddd; padding: 5px;">';
                $('#' + fieldId).after(previewHtml);
            }
            
            // Feedback visual
            $('#' + fieldId).trigger('change');
        });
        
        // Abre o media uploader
        mediaUploader.open();
    };
    
    /**
     * Remove imagem
     */
    window.haklaiRemoveImage = function(fieldId) {
        $('#' + fieldId).val('');
        $('#' + fieldId + '_preview').remove();
    };
    
})(jQuery);

