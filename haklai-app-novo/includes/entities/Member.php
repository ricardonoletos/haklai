<?php
/**
 * Entity: Member
 * Representa um membro da igreja
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
 * Classe Member
 * Entidade que representa um membro
 */
class Member {
    
    public $id;
    public $wp_post_id;
    public $name;
    public $email;
    public $phone;
    public $birth_date;
    public $gender;
    public $address;
    public $city;
    public $state;
    public $zip_code;
    public $role_level;
    public $cell_id;
    public $leader_id;
    public $network_id;
    public $baptism_status;
    public $baptism_date;
    public $is_visitor;
    public $visitor_count;
    public $status;
    public $last_attendance_date;
    public $total_attendances;
    public $consecutive_absences;
    public $photo_url;
    public $skill;
    public $encontro_deus;
    public $formacoes;
    public $notes;
    public $created_at;
    public $updated_at;
    public $created_by;
    public $updated_by;
    
    /**
     * Construtor
     * 
     * @param array|object $data Dados do membro
     */
    public function __construct($data = null) {
        if ($data) {
            $this->from_array($data);
        }
    }
    
    /**
     * Preenche propriedades a partir de array
     * 
     * @param array|object $data Dados do membro
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
        
        // Converte formacoes se for string JSON
        if (isset($data['formacoes']) && is_string($data['formacoes'])) {
            $this->formacoes = json_decode($data['formacoes'], true) ?: array();
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
     * Verifica se membro tem formação específica
     * 
     * @param string $formation Código da formação (cme, ctl, stp)
     * @return bool
     */
    public function has_formation($formation) {
        if (!is_array($this->formacoes)) {
            return false;
        }
        return in_array(strtolower($formation), array_map('strtolower', $this->formacoes));
    }
    
    /**
     * Verifica se é Frequentador Assíduo
     * 
     * @return bool
     */
    public function is_frequent_visitor() {
        return $this->baptism_status === 'frequent_visitor';
    }
    
    /**
     * Verifica se é visitante
     * 
     * @return bool
     */
    public function is_visitor() {
        return $this->is_visitor === 1 || $this->is_visitor === '1' || $this->baptism_status === 'visitor';
    }
    
    /**
     * Verifica se está ativo
     * 
     * @return bool
     */
    public function is_active() {
        return $this->status === 'active';
    }
}

