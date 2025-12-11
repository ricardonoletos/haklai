<?php
/**
 * Repository: StatisticsRepository
 * Orquestra consultas de estatísticas usando outros repositories
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe StatisticsRepository
 * Repository para operações com estatísticas
 */
class StatisticsRepository {
    
    private $member_repository;
    private $formation_repository;
    
    /**
     * Construtor
     * 
     * @param MemberRepository|null $member_repository
     * @param FormationRepository|null $formation_repository
     */
    public function __construct($member_repository = null, $formation_repository = null) {
        $this->member_repository = $member_repository ?: new MemberRepository();
        $this->formation_repository = $formation_repository ?: new FormationRepository($this->member_repository);
    }
    
    /**
     * Obtém estatísticas gerais
     * 
     * @param array|null $user_cells Array de objetos de células do usuário
     * @return array Array com estatísticas
     */
    public function getGeneralStats($user_cells = null) {
        // Extrai IDs das células se fornecido
        $cell_ids = null;
        if (!empty($user_cells)) {
            $cell_ids = array();
            foreach ($user_cells as $cell) {
                if (is_object($cell) && isset($cell->ID)) {
                    $cell_ids[] = $cell->ID;
                } elseif (is_numeric($cell)) {
                    $cell_ids[] = intval($cell);
                }
            }
        }
        
        // Conta membros ativos
        $total_members = $this->member_repository->countActive($cell_ids);
        
        // Conta F.A (Frequentador Assíduo)
        $total_fa = $this->member_repository->countByBaptismStatus('frequent_visitor', $cell_ids);
        
        // Obtém porcentagens de formações
        $formations = $this->formation_repository->getPercentages($total_members, $cell_ids);
        
        // Conta visitantes dos últimos 30 dias
        $total_visitors_30days = $this->member_repository->countVisitorsLast30Days($cell_ids);
        
        // Conta batismos do último mês
        $total_baptisms = $this->countBaptismsLastMonth($cell_ids);
        
        return array(
            'total_members' => $total_members,
            'total_fa' => $total_fa,
            'formations' => $formations,
            'total_visitors_30days' => $total_visitors_30days,
            'total_baptisms' => $total_baptisms,
        );
    }
    
    /**
     * Conta batismos do último mês
     * 
     * @param array|null $cell_ids IDs das células para filtrar
     * @return int
     */
    private function countBaptismsLastMonth($cell_ids = null) {
        // FASE 4: Obtém tenant atual
        $tenant_manager = Haklai_Tenant_Manager::get_instance();
        $current_tenant_id = $tenant_manager->get_current_tenant_id();
        
        $args = array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_haklai_baptism_status',
                    'value' => 'member',
                    'compare' => '='
                ),
                array(
                    'key' => '_haklai_baptism_date',
                    'value' => date('Y-m-d', strtotime('1 month ago')),
                    'compare' => '>='
                )
            )
        );
        
        // FASE 4: Adiciona filtro de tenant
        if ($current_tenant_id) {
            $args['meta_query'][] = array(
                'key' => '_haklai_tenant_id',
                'value' => $current_tenant_id,
                'compare' => '='
            );
        }
        
        if (!empty($cell_ids)) {
            $args['meta_query'][] = array(
                'key' => '_haklai_cell_id',
                'value' => $cell_ids,
                'compare' => 'IN'
            );
        }
        
        return count(get_posts($args));
    }
}

