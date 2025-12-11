<?php
/**
 * Repository: FormationRepository
 * Gerencia acesso aos dados de formações
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
 * Classe FormationRepository
 * Repository para operações com formações
 */
class FormationRepository {
    
    private $member_repository;
    
    /**
     * Construtor
     * 
     * @param MemberRepository|null $member_repository
     */
    public function __construct($member_repository = null) {
        $this->member_repository = $member_repository ?: new MemberRepository();
    }
    
    /**
     * Conta formações por tipo
     * 
     * @param string $type Tipo da formação (cme, ctl, stp)
     * @param array|null $cell_ids IDs das células para filtrar
     * @return int
     */
    public function countByType($type, $cell_ids = null) {
        $members = $this->member_repository->findAllActive($cell_ids);
        $count = 0;
        
        foreach ($members as $member) {
            if ($member->has_formation($type)) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Calcula porcentagens de formações
     * 
     * @param int $total_members Total de membros
     * @param array|null $cell_ids IDs das células para filtrar
     * @return array Array com contagens e porcentagens
     */
    public function getPercentages($total_members, $cell_ids = null) {
        if ($total_members === 0) {
            return array(
                'cme' => array('count' => 0, 'percentage' => 0),
                'ctl' => array('count' => 0, 'percentage' => 0),
                'stp' => array('count' => 0, 'percentage' => 0)
            );
        }
        
        $cme_count = $this->countByType(Formation::TYPE_CME, $cell_ids);
        $ctl_count = $this->countByType(Formation::TYPE_CTL, $cell_ids);
        $stp_count = $this->countByType(Formation::TYPE_STP, $cell_ids);
        
        return array(
            'cme' => array(
                'count' => $cme_count,
                'percentage' => round(($cme_count / $total_members) * 100, 1)
            ),
            'ctl' => array(
                'count' => $ctl_count,
                'percentage' => round(($ctl_count / $total_members) * 100, 1)
            ),
            'stp' => array(
                'count' => $stp_count,
                'percentage' => round(($stp_count / $total_members) * 100, 1)
            )
        );
    }
    
    /**
     * Busca formações de um membro
     * 
     * @param int $member_id ID do membro
     * @return array Array de objetos Formation
     */
    public function getFormationsByMember($member_id) {
        $member = $this->member_repository->findById($member_id);
        
        if (!$member || !is_array($member->formacoes)) {
            return array();
        }
        
        $formations = array();
        
        foreach ($member->formacoes as $formation_code) {
            $formation = new Formation($formation_code);
            if ($formation->code) {
                $formations[] = $formation;
            }
        }
        
        return $formations;
    }
    
    /**
     * Retorna todas as formações disponíveis
     * 
     * @return array
     */
    public function getAllFormations() {
        return Formation::get_all_formations();
    }
}

