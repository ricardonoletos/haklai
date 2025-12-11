<?php
/**
 * Repository: MemberRepository
 * Gerencia acesso aos dados de membros
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
 * Classe MemberRepository
 * Repository para operações com membros
 */
class MemberRepository {
    
    /**
     * Tenant Manager
     * 
     * @var Haklai_Tenant_Manager
     */
    private $tenant_manager;
    
    /**
     * Construtor
     */
    public function __construct() {
        $this->tenant_manager = Haklai_Tenant_Manager::get_instance();
    }
    
    /**
     * Busca membro por ID (CPT)
     * 
     * @param int $id ID do post CPT
     * @return Member|null
     */
    public function findById($id) {
        $post = get_post($id);
        
        if (!$post || $post->post_type !== 'haklai_member') {
            return null;
        }
        
        // FASE 4: Verifica se pertence ao tenant atual
        $current_tenant_id = $this->tenant_manager->get_current_tenant_id();
        if ($current_tenant_id) {
            $member_tenant_id = get_post_meta($id, '_haklai_tenant_id', true);
            if ($member_tenant_id != $current_tenant_id) {
                return null; // Não pertence ao tenant atual
            }
        }
        
        return $this->post_to_member($post);
    }
    
    /**
     * Busca membros por critérios
     * 
     * @param array $criteria Critérios de busca
     * @return array Array de objetos Member
     */
    public function findBy(array $criteria = array()) {
        $defaults = array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array()
        );
        
        $args = wp_parse_args($criteria, $defaults);
        
        // FASE 4: Adiciona filtro de tenant se não especificado
        $current_tenant_id = $this->tenant_manager->get_current_tenant_id();
        if ($current_tenant_id && !isset($criteria['skip_tenant_filter'])) {
            $args['meta_query'][] = array(
                'key' => '_haklai_tenant_id',
                'value' => $current_tenant_id,
                'compare' => '='
            );
        }
        
        // Adiciona filtro de status se não especificado
        if (!isset($criteria['meta_query_status'])) {
            $args['meta_query'][] = array(
                'key' => '_haklai_status',
                'value' => 'active',
                'compare' => '='
            );
        }
        
        $posts = get_posts($args);
        $members = array();
        
        foreach ($posts as $post) {
            $members[] = $this->post_to_member($post);
        }
        
        return $members;
    }
    
    /**
     * Conta membros por status
     * 
     * @param string $status Status (active, inactive, etc)
     * @param array|null $cell_ids IDs das células para filtrar
     * @return int
     */
    public function countByStatus($status = 'active', $cell_ids = null) {
        $args = array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_haklai_status',
                    'value' => $status,
                    'compare' => '='
                )
            )
        );
        
        // FASE 4: Adiciona filtro de tenant
        $current_tenant_id = $this->tenant_manager->get_current_tenant_id();
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
    
    /**
     * Conta membros por status de batismo
     * 
     * @param string $baptism_status Status de batismo
     * @param array|null $cell_ids IDs das células para filtrar
     * @return int
     */
    public function countByBaptismStatus($baptism_status, $cell_ids = null) {
        $args = array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_haklai_status',
                    'value' => 'active',
                    'compare' => '='
                ),
                array(
                    'key' => '_haklai_baptism_status',
                    'value' => $baptism_status,
                    'compare' => '='
                )
            )
        );
        
        // FASE 4: Adiciona filtro de tenant
        $current_tenant_id = $this->tenant_manager->get_current_tenant_id();
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
    
    /**
     * Conta visitantes dos últimos 30 dias
     * 
     * @param array|null $cell_ids IDs das células para filtrar
     * @return int
     */
    public function countVisitorsLast30Days($cell_ids = null) {
        $date_30_days_ago = date('Y-m-d', strtotime('-30 days'));
        
        $args = array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'date_query' => array(
                array(
                    'after' => $date_30_days_ago,
                    'inclusive' => true
                )
            ),
            'meta_query' => array(
                array(
                    'key' => '_haklai_status',
                    'value' => 'active',
                    'compare' => '='
                ),
                array(
                    'key' => '_haklai_is_visitor',
                    'value' => '1',
                    'compare' => '='
                )
            )
        );
        
        // FASE 4: Adiciona filtro de tenant
        $current_tenant_id = $this->tenant_manager->get_current_tenant_id();
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
    
    /**
     * Conta membros ativos
     * 
     * @param array|null $cell_ids IDs das células para filtrar
     * @return int
     */
    public function countActive($cell_ids = null) {
        return $this->countByStatus('active', $cell_ids);
    }
    
    /**
     * Busca todos os membros ativos
     * 
     * @param array|null $cell_ids IDs das células para filtrar
     * @return array Array de objetos Member
     */
    public function findAllActive($cell_ids = null) {
        $criteria = array(
            'meta_query_status' => true // Evita duplicação do filtro
        );
        
        if (!empty($cell_ids)) {
            $criteria['meta_query'] = array(
                array(
                    'key' => '_haklai_cell_id',
                    'value' => $cell_ids,
                    'compare' => 'IN'
                )
            );
        }
        
        return $this->findBy($criteria);
    }
    
    /**
     * Converte post CPT para objeto Member
     * 
     * @param WP_Post $post Post do WordPress
     * @return Member
     */
    private function post_to_member($post) {
        $member = new Member();
        
        $member->id = $post->ID;
        $member->wp_post_id = $post->ID;
        $member->name = $post->post_title;
        $member->email = get_post_meta($post->ID, '_haklai_email', true);
        $member->phone = get_post_meta($post->ID, '_haklai_phone', true);
        $member->birth_date = get_post_meta($post->ID, '_haklai_birth_date', true);
        $member->gender = get_post_meta($post->ID, '_haklai_gender', true);
        $member->address = get_post_meta($post->ID, '_haklai_address', true);
        $member->city = get_post_meta($post->ID, '_haklai_city', true);
        $member->state = get_post_meta($post->ID, '_haklai_state', true);
        $member->zip_code = get_post_meta($post->ID, '_haklai_zip_code', true);
        $member->role_level = intval(get_post_meta($post->ID, '_haklai_role_level', true));
        $member->cell_id = intval(get_post_meta($post->ID, '_haklai_cell_id', true));
        $member->leader_id = intval(get_post_meta($post->ID, '_haklai_leader_id', true));
        $member->network_id = intval(get_post_meta($post->ID, '_haklai_network_id', true));
        $member->baptism_status = get_post_meta($post->ID, '_haklai_baptism_status', true);
        $member->baptism_date = get_post_meta($post->ID, '_haklai_baptism_date', true);
        $member->is_visitor = get_post_meta($post->ID, '_haklai_is_visitor', true) === '1' ? 1 : 0;
        $member->visitor_count = intval(get_post_meta($post->ID, '_haklai_visitor_count', true));
        $member->status = get_post_meta($post->ID, '_haklai_status', true) ?: 'active';
        $member->last_attendance_date = get_post_meta($post->ID, '_haklai_last_attendance_date', true);
        $member->total_attendances = intval(get_post_meta($post->ID, '_haklai_total_attendances', true));
        $member->consecutive_absences = intval(get_post_meta($post->ID, '_haklai_consecutive_absences', true));
        $member->photo_url = get_post_meta($post->ID, '_haklai_photo', true);
        $member->skill = get_post_meta($post->ID, '_haklai_skill', true);
        $member->encontro_deus = get_post_meta($post->ID, '_haklai_encontro_deus', true);
        $member->formacoes = get_post_meta($post->ID, '_haklai_formacoes', true) ?: array();
        $member->notes = get_post_meta($post->ID, '_haklai_notes', true);
        $member->created_at = $post->post_date;
        $member->updated_at = $post->post_modified;
        $member->created_by = intval($post->post_author);
        
        return $member;
    }
}

