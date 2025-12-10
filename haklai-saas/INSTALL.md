# 🚀 GUIA DE INSTALAÇÃO - Haklai SaaS

Guia passo a passo para instalar o Haklai SaaS em hospedagem compartilhada.

---

## ✅ **PRÉ-REQUISITOS**

Antes de começar, certifique-se que seu servidor possui:

- ✅ PHP 8.1 ou superior
- ✅ MySQL 5.7+ ou MariaDB 10.3+
- ✅ Apache com mod_rewrite habilitado
- ✅ Extensões PHP:
  - PDO
  - PDO_MySQL
  - mbstring
  - openssl
  - curl
  - gd (para manipulação de imagens)

---

## 📦 **PASSO 1: UPLOAD DOS ARQUIVOS**

### **Via FTP:**
1. Conecte-se ao seu servidor via FTP (FileZilla, WinSCP, etc)
2. Faça upload de todos os arquivos para: `/public_html/` ou `/www/`
3. Aguarde a conclusão do upload

### **Via SSH (recomendado):**
```bash
cd /home/seu-usuario/public_html
git clone https://github.com/seu-repo/haklai-saas.git
cd haklai-saas
```

---

## 🗄️ **PASSO 2: CRIAR BANCO DE DADOS**

### **No cPanel/PHPMyAdmin:**

1. Acesse **cPanel → MySQL Databases**
2. **Criar novo banco:**
   - Nome: `haklai_saas` (ou outro de sua preferência)
3. **Criar usuário:**
   - Nome: `haklai_user`
   - Senha: Gere uma senha forte
4. **Adicionar usuário ao banco:**
   - Selecione o usuário e o banco
   - Marque "TODOS OS PRIVILÉGIOS"
5. **Importar schema:**
   - PHPMyAdmin → Importar
   - Selecione `database/schema.sql`
   - Clique em "Executar"

**Anote esses dados**, você vai precisar!

---

## ⚙️ **PASSO 3: CONFIGURAR AMBIENTE**

### **Criar arquivo .env:**

```bash
# Copie o arquivo de exemplo
cp .env.example .env
```

### **Editar .env:**

```env
# =================================================================
# CONFIGURAÇÕES DO BANCO DE DADOS
# =================================================================
DB_HOST=localhost
DB_PORT=3306
DB_NAME=haklai_saas
DB_USER=haklai_user
DB_PASS=SUA_SENHA_AQUI

# =================================================================
# CONFIGURAÇÕES DA APLICAÇÃO
# =================================================================
APP_NAME="Haklai SaaS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seusite.com
APP_KEY=

# =================================================================
# CONFIGURAÇÕES DE EMAIL (SMTP para 2FA)
# =================================================================
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha-de-app-do-gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@seusite.com
MAIL_FROM_NAME="Haklai SaaS"

# =================================================================
# GOOGLE MAPS API (para mapa de células)
# =================================================================
GOOGLE_MAPS_API_KEY=SUA_CHAVE_GOOGLE_MAPS_API

# =================================================================
# SEGURANÇA
# =================================================================
SESSION_LIFETIME=10080
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_DURATION=30
```

### **🔑 Gerar APP_KEY:**

Execute no terminal ou online em base64encode.org:
```bash
php -r "echo 'base64:' . base64_encode(random_bytes(32));"
```

Cole o resultado em `APP_KEY=`

---

## 📧 **PASSO 4: CONFIGURAR EMAIL (Gmail)**

### **Criar senha de app do Gmail:**

1. Acesse: https://myaccount.google.com/security
2. Ative **Verificação em duas etapas**
3. Vá em **Senhas de app**
4. Selecione "Outro (nome personalizado)"
5. Digite "Haklai SaaS"
6. **Copie a senha gerada** (16 caracteres)
7. Cole em `MAIL_PASSWORD=` no .env

---

## 🗺️ **PASSO 5: CONFIGURAR GOOGLE MAPS API**

### **Criar chave de API:**

1. Acesse: https://console.cloud.google.com/
2. Crie um novo projeto: "Haklai SaaS"
3. Ative **Maps JavaScript API** e **Geocoding API**
4. Vá em **Credenciais → Criar credenciais → Chave de API**
5. **Copie a chave**
6. Cole em `GOOGLE_MAPS_API_KEY=` no .env

**Restrinja a chave:**
- Restrições HTTP: Adicione seu domínio (seusite.com)

---

## 🔐 **PASSO 6: PERMISSÕES**

Configure as permissões corretas:

```bash
# Via SSH
chmod -R 755 storage/
chmod -R 755 public/uploads/
chmod 644 .env
chmod 644 config/*

# Via FTP
# Clique com botão direito → Permissões → 755
```

---

## 👤 **PASSO 7: CRIAR SUPER ADMIN**

### **Opção A: Via navegador**

1. Acesse: `https://seusite.com/install`
2. Preencha o formulário:
   - Nome completo
   - Email
   - Senha
3. Clique em "Criar Super Admin"

### **Opção B: Via SQL (PHPMyAdmin)**

Execute este SQL:

```sql
-- Insere tenant padrão
INSERT INTO tenants (name, slug, email, subscription_status, is_active, created_at, updated_at) 
VALUES ('Minha Igreja', 'minha-igreja', 'admin@igreja.com', 'active', 1, NOW(), NOW());

-- Anote o ID gerado (exemplo: 1)

-- Insere super admin
INSERT INTO users (
  tenant_id, 
  email, 
  password_hash, 
  full_name, 
  role_level, 
  is_active, 
  email_verified, 
  created_at, 
  updated_at
) VALUES (
  1, -- ID do tenant criado acima
  'admin@igreja.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: password
  'Administrador',
  99, -- Super Admin
  1,
  1,
  NOW(),
  NOW()
);
```

**⚠️ IMPORTANTE:** Troque a senha após o primeiro login!

---

## ✅ **PASSO 8: TESTAR INSTALAÇÃO**

1. Acesse: `https://seusite.com/`
2. Você deve ver a **página institucional**
3. Clique em "Login"
4. Entre com:
   - Email: admin@igreja.com
   - Senha: password (ou a que você definiu)
5. **Troque a senha imediatamente!**

---

## 🔒 **PASSO 9: SEGURANÇA PÓS-INSTALAÇÃO**

### **Obrigatório:**

1. **Troque a senha padrão**
2. **Delete ou proteja:**
   ```bash
   rm install.php
   rm INSTALL.md
   ```
3. **Ative HTTPS** (Let's Encrypt via cPanel)
4. **Configure backup automático** (cPanel Backup)

### **Recomendado:**

```env
# .env production
APP_ENV=production
APP_DEBUG=false
```

---

## 🐛 **PROBLEMAS COMUNS**

### **Erro: "Não foi possível conectar ao banco"**
✅ Verifique credenciais no `.env`  
✅ Teste conexão no PHPMyAdmin  
✅ Verifique se o usuário tem permissões  

### **Erro: "500 Internal Server Error"**
✅ Verifique permissões (755 nas pastas)  
✅ Veja logs: `storage/logs/`  
✅ Ative display_errors temporariamente  

### **Erro: "2FA não envia email"**
✅ Verifique senha de app do Gmail  
✅ Teste envio em: `https://seusite.com/test-email`  
✅ Veja logs de email  

### **Mapa não carrega:**
✅ Verifique `GOOGLE_MAPS_API_KEY`  
✅ Ative as APIs necessárias  
✅ Confira restrições da chave  

---

## 📞 **SUPORTE**

Se precisar de ajuda:

📧 Email: contato@ruthes.dev  
🌐 Website: https://ruthes.dev  

---

## ✅ **CHECKLIST FINAL**

- [ ] Upload completo dos arquivos
- [ ] Banco de dados criado e schema importado
- [ ] Arquivo .env configurado corretamente
- [ ] APP_KEY gerado
- [ ] Email SMTP configurado
- [ ] Google Maps API configurada
- [ ] Permissões corretas (755/644)
- [ ] Super admin criado
- [ ] Login testado com sucesso
- [ ] Senha padrão alterada
- [ ] HTTPS ativado
- [ ] install.php removido

---

**🎉 Parabéns! Sua instalação está completa!**

Acesse o dashboard e comece a configurar sua organização.

---

Desenvolvido com ❤️ por Jefter Ruthes
