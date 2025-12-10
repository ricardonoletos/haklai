<?php
/**
 * Entity: Role
 * Representa uma função/role hierárquica
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
 * Classe Role
 * Entidade que representa uma função/role
 */
class Role {
    
    public $id;
    public $code;
    public $name;
    public $level;
    public $description;
    public $capabilities;
    public $is_active;
    public $created_at;
    public $updated_at;
    
    /**
     * Construtor
     * 
     * @param array|object $data Dados da role
     */
    public function __construct($data = null) {
        if ($data) {
            $this->from_array($data);
        }
    }
    
    /**
     * Preenche propriedades a partir de array
     * 
     * @param array|object $data Dados da role
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
        
        // Converte capabilities se for string JSON
        if (isset($data['capabilities']) && is_string($data['capabilities'])) {
            $this->capabilities = json_decode($data['capabilities'], true) ?: array();
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
     * Verifica se role tem capability específica
     * 
     * @param string $capability Nome da capability
     * @return bool
     */
    public function has_capability($capability) {
        if (!is_array($this->capabilities)) {
            return false;
        }
        return in_array($capability, $this->capabilities);
    }
}

