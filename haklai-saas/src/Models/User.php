<?php
/**
 * User Model
 * Haklai SaaS - Desenvolvido por: Jefter Ruthes
 */

namespace Haklai\Models;

use Haklai\Core\Database;

class User
{
    public ?int $id = null;
    public int $tenant_id;
    public ?int $member_id = null;
    public string $email;
    public string $password_hash;
    public string $full_name;
    public int $role_level = 0;
    public bool $is_active = true;
    public bool $email_verified = false;
    public ?string $email_verification_token = null;
    public ?string $email_verified_at = null;
    public ?string $password_reset_token = null;
    public ?string $password_reset_expires = null;
    public ?string $two_factor_secret = null;
    public bool $two_factor_enabled = false;
    public ?string $two_factor_code = null;
    public ?string $two_factor_expires = null;
    public ?string $last_login_at = null;
    public ?string $last_login_ip = null;
    public int $login_attempts = 0;
    public ?string $locked_until = null;
    public ?string $remember_token = null;
    public ?string $session_token = null;
    public ?string $session_expires_at = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
    
    public static function find(int $id): ?self
    {
        $db = Database::getInstance();
        $data = $db->queryOne("SELECT * FROM users WHERE id = ?", [$id]);
        
        if (!$data) return null;
        
        $user = new self();
        foreach ($data as $key => $value) {
            if (property_exists($user, $key)) {
                $user->$key = $value;
            }
        }
        return $user;
    }
    
    public static function findByEmail(string $email): ?self
    {
        $db = Database::getInstance();
        $data = $db->queryOne("SELECT * FROM users WHERE email = ?", [$email]);
        
        if (!$data) return null;
        
        $user = new self();
        foreach ($data as $key => $value) {
            if (property_exists($user, $key)) {
                $user->$key = $value;
            }
        }
        return $user;
    }
    
    public function save(): bool
    {
        $db = Database::getInstance();
        
        $data = [
            'tenant_id' => $this->tenant_id,
            'member_id' => $this->member_id,
            'email' => $this->email,
            'password_hash' => $this->password_hash,
            'full_name' => $this->full_name,
            'role_level' => $this->role_level,
            'is_active' => $this->is_active ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($this->id) {
            $db->update('users', $data, 'id = :id', ['id' => $this->id]);
            return true;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->id = $db->insert('users', $data);
            return $this->id > 0;
        }
    }
    
    public function getRoleName(): string
    {
        return match($this->role_level) {
            0 => 'Membro',
            1 => 'Líder de Célula',
            2 => 'Discipulador',
            3 => 'Pastor de Rede',
            4 => 'Pastor Senior',
            5 => 'Pastor Supervisor',
            99 => 'Super Admin',
            default => 'Desconhecido'
        };
    }
}
