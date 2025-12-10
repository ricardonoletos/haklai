<?php
/**
 * Repository: TenantRepository
 * Gerencia acesso aos dados de tenants
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
 * Classe TenantRepository
 * Repository para operações com tenants
 */
class TenantRepository {
    
    /**
     * Cria novo tenant
     * 
     * @param array $data Dados do tenant
     * @return int|false ID do tenant criado ou false em caso de erro
     */
    public function create($data) {
        global $wpdb;
        $table_name = Haklai_DB::get_table_name('tenants');
        
        $defaults = array(
            'name' => '',
            'code' => '',
            'location' => '',
            'supervisor_contact' => '',
            'supervisor_pastor_id' => null,
            'is_active' => 1,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'created_by' => get_current_user_id()
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Gera código único se não fornecido
        if (empty($data['code'])) {
            $data['code'] = $this->generate_unique_code($data['name']);
        }
        
        // Valida código único
        if ($this->codeExists($data['code'])) {
            return false; // Código já existe
        }
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'code' => strtoupper(sanitize_text_field($data['code'])),
                'location' => sanitize_text_field($data['location']),
                'supervisor_contact' => sanitize_text_field($data['supervisor_contact']),
                'supervisor_pastor_id' => $data['supervisor_pastor_id'] ? intval($data['supervisor_pastor_id']) : null,
                'is_active' => $data['is_active'] ? 1 : 0,
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
                'created_by' => $data['created_by'] ? intval($data['created_by']) : null
            ),
            array('%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d')
        );
        
        if ($result === false) {
            error_log('Haklai TenantRepository: Erro ao criar tenant - ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Busca tenant por ID
     * 
     * @param int $id ID do tenant
     * @return Tenant|null
     */
    public function findById($id) {
        global $wpdb;
        $table_name = Haklai_DB::get_table_name('tenants');
        
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $id
        ));
        
        if (!$row) {
            return null;
        }
        
        return new Tenant($row);
    }
    
    /**
     * Busca tenant por código
     * 
     * @param string $code Código do tenant
     * @return Tenant|null
     */
    public function findByCode($code) {
        global $wpdb;
        $table_name = Haklai_DB::get_table_name('tenants');
        
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE code = %s",
            strtoupper($code)
        ));
        
        if (!$row) {
            return null;
        }
        
        return new Tenant($row);
    }
    
    /**
     * Obtém tenant atual do usuário
     * 
     * @param int $user_id ID do usuário WordPress
     * @return Tenant|null
     */
    public function getCurrentTenant($user_id) {
        // Primeiro tenta obter da sessão/meta do usuário
        $tenant_id = get_user_meta($user_id, '_haklai_current_tenant_id', true);
        
        if ($tenant_id) {
            return $this->findById($tenant_id);
        }
        
        // Se não tiver na sessão, busca pelo supervisor_pastor_id
        global $wpdb;
        $table_name = Haklai_DB::get_table_name('tenants');
        
        // Busca tenant onde o usuário é supervisor
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE supervisor_pastor_id = %d AND is_active = 1 LIMIT 1",
            $user_id
        ));
        
        if ($row) {
            return new Tenant($row);
        }
        
        return null;
    }
    
    /**
     * Define tenant atual para o usuário
     * 
     * @param int $tenant_id ID do tenant
     * @param int|null $user_id ID do usuário (null = usuário atual)
     * @return bool
     */
    public function setCurrentTenant($tenant_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }
        
        // Valida que o tenant existe
        $tenant = $this->findById($tenant_id);
        if (!$tenant || !$tenant->is_active()) {
            return false;
        }
        
        // Salva no meta do usuário
        update_user_meta($user_id, '_haklai_current_tenant_id', $tenant_id);
        
        return true;
    }
    
    /**
     * Lista todos os tenants ativos
     * 
     * @return array Array de objetos Tenant
     */
    public function findAllActive() {
        global $wpdb;
        $table_name = Haklai_DB::get_table_name('tenants');
        
        $results = $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE is_active = 1 ORDER BY name ASC"
        );
        
        $tenants = array();
        foreach ($results as $row) {
            $tenants[] = new Tenant($row);
        }
        
        return $tenants;
    }
    
    /**
     * Verifica se código já existe
     * 
     * @param string $code Código do tenant
     * @return bool
     */
    private function codeExists($code) {
        global $wpdb;
        $table_name = Haklai_DB::get_table_name('tenants');
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE code = %s",
            strtoupper($code)
        ));
        
        return $count > 0;
    }
    
    /**
     * Gera código único baseado no nome
     * 
     * @param string $name Nome do tenant
     * @return string Código único
     */
    private function generate_unique_code($name) {
        // Remove acentos e caracteres especiais
        $code = sanitize_title($name);
        $code = strtoupper(substr($code, 0, 10));
        
        // Remove hífens e espaços
        $code = str_replace(array('-', ' '), '', $code);
        
        // Se código estiver vazio, usa padrão
        if (empty($code)) {
            $code = 'TENANT' . time();
        }
        
        // Garante unicidade
        $original_code = $code;
        $counter = 1;
        while ($this->codeExists($code)) {
            $code = $original_code . $counter;
            $counter++;
        }
        
        return $code;
    }
}

