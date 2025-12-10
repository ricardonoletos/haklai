<?php
/**
 * Entity: Formation
 * Representa uma formação/curso
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
 * Classe Formation
 * Entidade que representa uma formação
 */
class Formation {
    
    const TYPE_CME = 'cme';
    const TYPE_CTL = 'ctl';
    const TYPE_STP = 'stp';
    
    public $code;
    public $name;
    public $description;
    public $duration;
    public $is_active;
    
    /**
     * Construtor
     * 
     * @param string $code Código da formação
     * @param string $name Nome da formação
     */
    public function __construct($code = null, $name = null) {
        $this->code = $code;
        $this->name = $name;
        
        if ($code) {
            $this->load_from_code($code);
        }
    }
    
    /**
     * Carrega dados a partir do código
     * 
     * @param string $code Código da formação
     */
    private function load_from_code($code) {
        $formations = self::get_all_formations();
        
        if (isset($formations[$code])) {
            $this->code = $code;
            $this->name = $formations[$code]['name'];
            $this->description = $formations[$code]['description'] ?? '';
        }
    }
    
    /**
     * Retorna todas as formações disponíveis
     * 
     * @return array
     */
    public static function get_all_formations() {
        return array(
            self::TYPE_CME => array(
                'code' => self::TYPE_CME,
                'name' => __('Curso de Maturidade Espiritual (CME)', 'haklai-app'),
                'description' => __('Formação básica em maturidade espiritual', 'haklai-app')
            ),
            self::TYPE_CTL => array(
                'code' => self::TYPE_CTL,
                'name' => __('Curso de Treinamento de Líderes (CTL)', 'haklai-app'),
                'description' => __('Formação para liderança de células', 'haklai-app')
            ),
            self::TYPE_STP => array(
                'code' => self::TYPE_STP,
                'name' => __('Seminário Teológico Pastoral (STP)', 'haklai-app'),
                'description' => __('Formação avançada em teologia pastoral', 'haklai-app')
            )
        );
    }
    
    /**
     * Retorna nome formatado
     * 
     * @return string
     */
    public function get_formatted_name() {
        return strtoupper($this->code);
    }
}

