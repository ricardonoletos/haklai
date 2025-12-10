<?php
/**
 * Entity: Tenant
 * Representa um tenant (grupo/localidade) para multi-tenancy
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
 * Classe Tenant
 * Entidade que representa um tenant (grupo/localidade)
 */
class Tenant {
    
    public $id;
    public $name;
    public $code;
    public $location;
    public $supervisor_contact;
    public $supervisor_pastor_id;
    public $is_active;
    public $created_at;
    public $updated_at;
    public $created_by;
    
    /**
     * Construtor
     * 
     * @param array|object $data Dados do tenant
     */
    public function __construct($data = null) {
        if ($data) {
            $this->from_array($data);
        }
    }
    
    /**
     * Preenche propriedades a partir de array
     * 
     * @param array|object $data Dados do tenant
     */
    public function from_array($data) {
        if (is_object($data)) {
            $data = (array) $data;
        }
        
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Converte para array
     * 
     * @return array
     */
    public function to_array() {
        return get_object_vars($this);
    }
    
    /**
     * Verifica se tenant está ativo
     * 
     * @return bool
     */
    public function is_active() {
        return $this->is_active === 1 || $this->is_active === true;
    }
}

