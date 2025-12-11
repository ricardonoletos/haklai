# 🏛️ Haklai SaaS - Church Management System

Sistema completo de gestão de igrejas com controle de células, presenças, relatórios e hierarquia ministerial.

**Desenvolvido por:** Jefter Ruthes  
**Versão:** 1.0.0  
**Licença:** Proprietária

---

## 📋 **FUNCIONALIDADES PRINCIPAIS**

### ✅ **Sistema Multi-Tenant**
- Múltiplas organizações/igrejas isoladas
- Planos: Free, Basic, Premium, Enterprise
- Gerenciamento completo de assinaturas

### 👥 **Gestão de Membros**
- Cadastro completo com foto
- Hierarquia: Membro → Líder → Discipulador → Pastor → Supervisor
- Controle de visitantes e Frequentadores Assíduos (F.A.)
- Formações (CTL, CME, STP)
- Batismo e Encontro com Deus

### 🏠 **Sistema de Células**
- Células com localização (Google Maps)
- Agrupamento em Redes
- Controle de líderes e discipuladores
- Histórico completo de reuniões

### ✅ **Checkpoint (Sistema de Ponto)**
- Registro de presença em reuniões
- Associação completa com hierarquia
- Dados de local, anfitrião e data
- Interface otimizada para mobile

### 📊 **Dashboard e Relatórios**
- Relatórios de presença por período
- Relatórios de formações
- Ranking de células
- Crescimento mensal
- Membros faltosos
- Exportação PDF e Excel
- Gráficos interativos

### 🔐 **Segurança Avançada**
- Autenticação com 2FA via email
- Proteção contra SQL Injection
- Proteção XSS e CSRF
- Session hijacking prevention
- Logs de auditoria completos
- Senha criptografada (Bcrypt)

### 📱 **Mobile-First**
- Interface responsiva (Bootstrap 5)
- PWA ready (Service Workers)
- API REST para app mobile
- Tema claro/escuro

---

## 🗄️ **ESTRUTURA DO BANCO DE DADOS**

```
├── tenants              # Organizações/Igrejas
├── users                # Usuários do sistema
├── networks             # Redes de células
├── cells                # Células/Grupos
├── members              # Membros
├── formations           # Formações (CTL, CME, STP)
├── member_formations    # Formações dos membros
├── meetings             # Reuniões
├── attendance           # Presenças
├── reports              # Relatórios gerados
├── notifications        # Notificações
├── audit_logs           # Logs de auditoria
└── settings             # Configurações
```

---

## 🚀 **INSTALAÇÃO**

### **Requisitos:**
- PHP 8.1+
- MySQL 5.7+ ou MariaDB 10.3+
- Apache com mod_rewrite
- Extensões PHP: PDO, PDO_MySQL, mbstring, openssl, curl

### **Passo 1: Upload dos arquivos**
```bash
# Upload para servidor via FTP/SSH
# Estrutura: /public_html/haklai-saas/
```

### **Passo 2: Criar banco de dados**
1. Acesse PHPMyAdmin
2. Crie um novo banco: `haklai_saas`
3. Importe o arquivo: `database/schema.sql`

### **Passo 3: Configurar ambiente**
```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Edite .env com seus dados
nano .env
```

**Configurações obrigatórias:**
```env
DB_HOST=localhost
DB_NAME=haklai_saas
DB_USER=seu_usuario
DB_PASS=sua_senha

MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=seu-email@gmail.com
MAIL_PASSWORD=sua-senha-app

GOOGLE_MAPS_API_KEY=sua-chave-api
```

### **Passo 4: Permissões**
```bash
chmod -R 755 storage/
chmod -R 755 public/uploads/
```

### **Passo 5: Criar Super Admin**
```bash
# Execute o script de instalação
php install.php
```

---

## 📁 **ESTRUTURA DE ARQUIVOS**

```
haklai-saas/
├── config/                 # Configurações
│   ├── app.php
│   └── database.php
├── database/               # SQL schemas
│   └── schema.sql
├── public/                 # Pasta pública (document root)
│   ├── index.php          # Entry point
│   ├── assets/            # CSS, JS, Images
│   └── uploads/           # Arquivos enviados
├── src/                   # Código fonte
│   ├── Core/              # Classes fundamentais
│   ├── Models/            # Modelos de dados
│   ├── Controllers/       # Lógica de negócio
│   ├── Views/             # Templates
│   ├── Services/          # Serviços (Email, Reports, etc)
│   └── Middleware/        # Middlewares
├── storage/               # Armazenamento
│   ├── logs/              # Logs do sistema
│   ├── sessions/          # Sessões
│   └── cache/             # Cache
├── .env                   # Configurações (NÃO VERSIONAR)
├── .htaccess              # Rewrite rules
└── README.md
```

---

## 🔑 **NÍVEIS DE ACESSO**

| Nível | Papel | Permissões |
|-------|-------|------------|
| 0 | Membro | Visualizar próprios dados |
| 1 | Líder de Célula | Gerenciar célula, marcar presenças |
| 2 | Discipulador | Gerenciar múltiplas células |
| 3 | Pastor de Rede | Gerenciar rede, visualizar relatórios |
| 4 | Pastor Senior | Gerenciar múltiplas redes |
| 5 | Pastor Supervisor | Acesso completo à organização |
| 99 | Super Admin | Acesso completo ao sistema |

---

## 📱 **API REST (Mobile App)**

### **Autenticação:**
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "usuario@igreja.com",
  "password": "senha123"
}

Response:
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "João Silva",
    "role_level": 1
  }
}
```

### **Endpoints principais:**
```
GET    /api/meetings              # Lista reuniões
POST   /api/meetings              # Cria reunião
GET    /api/meetings/{id}         # Detalhes da reunião
POST   /api/attendance            # Registra presença
GET    /api/members               # Lista membros
GET    /api/cells                 # Lista células
GET    /api/reports/{type}        # Gera relatório
```

---

## 🛡️ **SEGURANÇA**

### **Práticas implementadas:**
✅ Prepared Statements (PDO)  
✅ Bcrypt para senhas  
✅ 2FA obrigatório para admins  
✅ CSRF tokens  
✅ XSS protection  
✅ Session hijacking prevention  
✅ Rate limiting (login attempts)  
✅ Logs de auditoria  
✅ HTTPS ready  

### **Configurações recomendadas:**
```php
// .env production
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE=true
```

---

## 🎨 **PERSONALIZAÇÃO**

### **Temas:**
- Suporte a tema claro/escuro via toggle
- CSS customizável em `public/assets/css/`
- Logo do tenant dinâmico

### **Cores institucionais:**
```css
:root {
  --primary-color: #4A90E2;
  --secondary-color: #50C878;
  --danger-color: #E74C3C;
}
```

---

## 📧 **SUPORTE**

**Desenvolvedor:** Jefter Ruthes  
**Website:** https://ruthes.dev  
**Email:** contato@ruthes.dev  

---

## 📄 **LICENÇA**

Copyright © 2025 Jefter Ruthes. Todos os direitos reservados.

Este software é proprietário e não pode ser redistribuído sem autorização expressa.

---

## 🔄 **CHANGELOG**

### **v1.0.0** (10/12/2024)
- ✅ Sistema multi-tenant completo
- ✅ Autenticação com 2FA
- ✅ Gestão de células e membros
- ✅ Sistema de checkpoint (presenças)
- ✅ Dashboard com relatórios
- ✅ API REST para mobile
- ✅ Integração Google Maps
- ✅ Exportação PDF/Excel
- ✅ Tema claro/escuro

---

**Feito com ❤️ para gestão de igrejas modernas**
