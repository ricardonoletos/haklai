/**
 * Haklai Shortcodes V2 - JavaScript específico para templates V2
 * 
 * Projeto: Haklai Church — Plugin WordPress Modular
 * Desenvolvido por: Ricardo Sarmento - https://linx.pt
 */

(function($) {
    'use strict';

    // Aguarda o documento estar pronto
    $(document).ready(function() {
        
        // Busca de anfitrião
        if ($('#anfitriaoSearch').length) {
            $('#anfitriaoSearch').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                const $results = $('#searchResults');
                
                if (searchTerm.length > 2 && window.haklaiCheckpoint && window.haklaiCheckpoint.members) {
                    // Filtra membros disponíveis
                    const availableMembers = window.haklaiCheckpoint.members.filter(member => 
                        member.name.toLowerCase().includes(searchTerm)
                    );
                    
                    if (availableMembers.length > 0) {
                        let html = '';
                        availableMembers.forEach(member => {
                            html += `<div class="haklai-search-result-item" onclick="selectAnfitriao('${member.name}')">${member.name}</div>`;
                        });
                        $results.html(html).addClass('show');
                    } else {
                        $results.removeClass('show');
                    }
                } else {
                    $results.removeClass('show');
                }
            });
            
            // Fecha resultados de busca ao clicar fora
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.haklai-search-container').length) {
                    $('#searchResults').removeClass('show');
                }
            });
        }
        
        // Função global para selecionar anfitrião
        window.selectAnfitriao = function(name) {
            $('#anfitriaoSearch').val(name);
            $('#searchResults').removeClass('show');
        };
        
        // Atualiza estatísticas quando a presença é alterada
        if (window.updateStats) {
            // Chama a função de atualização inicial
            setTimeout(function() {
                if (typeof updateStats === 'function') {
                    updateStats();
                }
            }, 100);
        }
        
    });

})(jQuery);

