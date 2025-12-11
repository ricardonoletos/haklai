<?php
/**
 * Tenant Manager
 * Gerencia operações relacionadas a tenants (multi-tenancy)
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
 * Classe Haklai_Tenant_Manager
 * Gerencia tenants e isolamento de dados
 */
class Haklai_Tenant_Manager {
    
    /**
     * Instância única da classe
     * 
     * @var Haklai_Tenant_Manager
     */
    private static $instance = null;
    
    /**
     * Repository de tenants
     * 
     * @var TenantRepository
     */
    private $tenant_repository;
    
    /**
     * ID do tenant atual (cache)
     * 
     * @var int|null
     */
    private $current_tenant_id = null;
    
    /**
     * Construtor privado (Singleton)
     */
    private function __construct() {
        $this->tenant_repository = new TenantRepository();
    }
    
    /**
     * Retorna instância única da classe
     * 
     * @return Haklai_Tenant_Manager
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtém ID do tenant atual
     * 
     * @param int|null $user_id ID do usuário (null = usuário atual)
     * @return int|null ID do tenant ou null se não encontrado
     */
    public function get_current_tenant_id($user_id = null) {
        // Retorna do cache se disponível
        if ($this->current_tenant_id !== null) {
            return $this->current_tenant_id;
        }
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return null;
        }
        
        // Busca tenant atual do usuário
        $tenant = $this->tenant_repository->getCurrentTenant($user_id);
        
        if ($tenant) {
            $this->current_tenant_id = $tenant->id;
            return $tenant->id;
        }
        
        return null;
    }
    
    /**
     * Define tenant atual
     * 
     * @param int $tenant_id ID do tenant
     * @param int|null $user_id ID do usuário (null = usuário atual)
     * @return bool
     */
    public function set_current_tenant($tenant_id, $user_id = null) {
        $result = $this->tenant_repository->setCurrentTenant($tenant_id, $user_id);
        
        if ($result) {
            // Limpa cache
            $this->current_tenant_id = null;
        }
        
        return $result;
    }
    
    /**
     * Verifica se usuário é Tenant Owner
     * 
     * @param int|null $user_id ID do usuário (null = usuário atual)
     * @return bool
     */
    public function user_is_tenant_owner($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return false;
        }
        
        $tenant = $this->tenant_repository->getCurrentTenant($user_id);
        
        if (!$tenant) {
            return false;
        }
        
        // Verifica se o usuário é o supervisor do tenant
        return $tenant->supervisor_pastor_id == $user_id;
    }
    
    /**
     * Obtém tenant atual (objeto completo)
     * 
     * @param int|null $user_id ID do usuário (null = usuário atual)
     * @return Tenant|null
     */
    public function get_current_tenant($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return $this->tenant_repository->getCurrentTenant($user_id);
    }
    
    /**
     * Cria tenant padrão para migração
     * 
     * @return int|false ID do tenant criado ou false
     */
    public function create_default_tenant() {
        // Verifica se já existe tenant padrão
        $default_tenant = $this->tenant_repository->findByCode('DEFAULT');
        
        if ($default_tenant) {
            return $default_tenant->id;
        }
        
        // Cria tenant padrão
        return $this->tenant_repository->create(array(
            'name' => __('Tenant Padrão', 'haklai-app'),
            'code' => 'DEFAULT',
            'location' => __('Localização não especificada', 'haklai-app'),
            'supervisor_contact' => '',
            'is_active' => 1
        ));
    }
    
    /**
     * Limpa cache do tenant atual
     */
    public function clear_cache() {
        $this->current_tenant_id = null;
    }
}

