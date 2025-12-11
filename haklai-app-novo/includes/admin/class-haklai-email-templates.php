<?php
/**
 * Gerenciador de Templates de Email
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

class Haklai_Email_Templates {
    
    /**
     * Templates disponíveis
     */
    private $templates = [
        'credentials' => [
            'name' => 'Credenciais de Acesso',
            'description' => 'Enviado quando um líder recebe credenciais de acesso',
            'variables' => ['{nome}', '{email}', '{usuario}', '{senha}', '{celula}', '{nivel}', '{site_url}']
        ],
        'welcome' => [
            'name' => 'Boas-Vindas',
            'description' => 'Enviado para novos membros cadastrados',
            'variables' => ['{nome}', '{celula}', '{lider}', '{data_cadastro}', '{site_url}']
        ],
        'meeting' => [
            'name' => 'Reunião Marcada',
            'description' => 'Enviado quando uma reunião é agendada',
            'variables' => ['{nome}', '{celula}', '{data}', '{hora}', '{endereco}', '{anfitriao}', '{site_url}']
        ],
        'report' => [
            'name' => 'Relatório Gerado',
            'description' => 'Enviado quando um relatório é finalizado',
            'variables' => ['{celula}', '{data}', '{presentes}', '{ausentes}', '{total}', '{taxa_presenca}', '{site_url}']
        ]
    ];
    
    /**
     * Obtém lista de templates
     */
    public function get_templates_list() {
        return $this->templates;
    }
    
    /**
     * Obtém configuração de template
     */
    public function get_template_config($template_key) {
        $subject = get_option("haklai_email_{$template_key}_subject", $this->get_default_subject($template_key));
        $body = get_option("haklai_email_{$template_key}_body", $this->get_default_body($template_key));
        
        return [
            'subject' => $subject,
            'body' => $body,
            'info' => $this->templates[$template_key] ?? []
        ];
    }
    
    /**
     * Salva configuração de template
     */
    public function save_template_config($template_key, $subject, $body) {
        update_option("haklai_email_{$template_key}_subject", sanitize_text_field($subject));
        update_option("haklai_email_{$template_key}_body", wp_kses_post($body));
        
        return true;
    }
    
    /**
     * Restaura template padrão
     */
    public function restore_default($template_key) {
        delete_option("haklai_email_{$template_key}_subject");
        delete_option("haklai_email_{$template_key}_body");
        
        return true;
    }
    
    /**
     * Assuntos padrão
     */
    private function get_default_subject($template_key) {
        $defaults = [
            'credentials' => 'Bem-vindo ao Sistema Haklai - Suas Credenciais de Acesso',
            'welcome' => 'Bem-vindo à ' . get_bloginfo('name'),
            'meeting' => 'Reunião de Célula Agendada - {celula}',
            'report' => 'Relatório da Reunião - {celula}'
        ];
        
        return $defaults[$template_key] ?? '';
    }
    
    /**
     * Corpos padrão
     */
    private function get_default_body($template_key) {
        switch ($template_key) {
            case 'credentials':
                return $this->get_credentials_default();
            case 'welcome':
                return $this->get_welcome_default();
            case 'meeting':
                return $this->get_meeting_default();
            case 'report':
                return $this->get_report_default();
            default:
                return '';
        }
    }
    
    private function get_credentials_default() {
        return '<p>Olá {nome},</p>
<p>Você foi cadastrado como líder no Sistema Haklai!</p>
<p><strong>Suas credenciais de acesso:</strong></p>
<ul>
    <li>Usuário: {usuario}</li>
    <li>Senha: {senha}</li>
    <li>Célula: {celula}</li>
    <li>Nível: {nivel}</li>
</ul>
<p>Acesse: {site_url}</p>
<p>Atenciosamente,<br>Equipe Haklai</p>';
    }
    
    private function get_welcome_default() {
        return '<p>Olá {nome},</p>
<p>Seja bem-vindo à {celula}!</p>
<p>É uma alegria tê-lo(a) conosco.</p>
<p>Seu líder de célula é: {lider}</p>
<p>Data de cadastro: {data_cadastro}</p>
<p>Atenciosamente,<br>Equipe Haklai</p>';
    }
    
    private function get_meeting_default() {
        return '<p>Olá {nome},</p>
<p>Uma nova reunião foi agendada!</p>
<p><strong>Detalhes:</strong></p>
<ul>
    <li>Célula: {celula}</li>
    <li>Data: {data}</li>
    <li>Hora: {hora}</li>
    <li>Local: {endereco}</li>
    <li>Anfitrião: {anfitriao}</li>
</ul>
<p>Esperamos você!</p>
<p>Atenciosamente,<br>Equipe Haklai</p>';
    }
    
    private function get_report_default() {
        return '<p>Olá,</p>
<p>O relatório da reunião foi gerado.</p>
<p><strong>Resumo:</strong></p>
<ul>
    <li>Célula: {celula}</li>
    <li>Data: {data}</li>
    <li>Presentes: {presentes}</li>
    <li>Ausentes: {ausentes}</li>
    <li>Total: {total}</li>
    <li>Taxa de Presença: {taxa_presenca}%</li>
</ul>
<p>Atenciosamente,<br>Equipe Haklai</p>';
    }
    
    /**
     * Processa variáveis no template
     */
    public static function process_variables($template, $variables) {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        return $template;
    }
}

