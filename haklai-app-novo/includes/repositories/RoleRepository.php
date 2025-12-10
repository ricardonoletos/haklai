<?php
/**
 * Repository: RoleRepository
 * Gerencia acesso aos dados de roles/funções
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
 * Classe RoleRepository
 * Repository para operações com roles/funções
 * 
 * Nota: Por enquanto, as roles são gerenciadas via WordPress roles/capabilities.
 * Este repository prepara a estrutura para futura migração para tabela dedicada.
 */
class RoleRepository {
    
    /**
     * Busca role por código
     * 
     * @param string $code Código da role
     * @return Role|null
     */
    public function findByCode($code) {
        // Por enquanto, mapeia códigos para roles do WordPress
        $role_mapping = $this->get_role_mapping();
        
        if (!isset($role_mapping[$code])) {
            return null;
        }
        
        $role_data = $role_mapping[$code];
        return new Role($role_data);
    }
    
    /**
     * Busca roles por nível hierárquico
     * 
     * @param int $level Nível hierárquico
     * @return array Array de objetos Role
     */
    public function findByLevel($level) {
        $all_roles = $this->findAll();
        $filtered = array();
        
        foreach ($all_roles as $role) {
            if ($role->level == $level) {
                $filtered[] = $role;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Lista todas as roles
     * 
     * @return array Array de objetos Role
     */
    public function findAll() {
        $role_mapping = $this->get_role_mapping();
        $roles = array();
        
        foreach ($role_mapping as $code => $data) {
            $roles[] = new Role($data);
        }
        
        return $roles;
    }
    
    /**
     * Retorna mapeamento de roles
     * 
     * @return array
     */
    private function get_role_mapping() {
        return array(
            'lider_celula' => array(
                'code' => 'lider_celula',
                'name' => __('Líder de Célula', 'haklai-app'),
                'level' => 1,
                'description' => __('Líder responsável por uma célula', 'haklai-app')
            ),
            'discipulador' => array(
                'code' => 'discipulador',
                'name' => __('Discipulador', 'haklai-app'),
                'level' => 2,
                'description' => __('Responsável por discipular líderes', 'haklai-app')
            ),
            'pastor_rede' => array(
                'code' => 'pastor_rede',
                'name' => __('Pastor de Rede', 'haklai-app'),
                'level' => 3,
                'description' => __('Pastor responsável por uma rede de células', 'haklai-app')
            ),
            'pastor_senior' => array(
                'code' => 'pastor_senior',
                'name' => __('Pastor Sênior', 'haklai-app'),
                'level' => 4,
                'description' => __('Pastor com responsabilidades amplas', 'haklai-app')
            ),
            'pastor_supervisor' => array(
                'code' => 'pastor_supervisor',
                'name' => __('Pastor Supervisor', 'haklai-app'),
                'level' => 5,
                'description' => __('Pastor supervisor (Tenant Owner)', 'haklai-app')
            )
        );
    }
}

