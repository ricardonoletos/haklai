# 📱 API REST - Haklai SaaS

Documentação completa da API REST para integração com aplicativos mobile.

---

## 🔑 **AUTENTICAÇÃO**

### **POST /api/auth/login**

Autentica usuário e retorna token de sessão.

**Request:**
```json
{
  "email": "usuario@igreja.com",
  "password": "senha123"
}
```

**Response (Sucesso):**
```json
{
  "success": true,
  "token": "abc123def456",
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@igreja.com",
    "role_level": 1,
    "tenant_id": 1
  }
}
```

**Response (2FA Necessário):**
```json
{
  "success": true,
  "requires_2fa": true,
  "message": "Código enviado para seu email"
}
```

---

### **POST /api/auth/verify-2fa**

Verifica código 2FA.

**Request:**
```json
{
  "code": "123456"
}
```

---

### **POST /api/auth/logout**

Encerra sessão.

**Headers:**
```
Authorization: Bearer {token}
```

---

## 👥 **MEMBROS**

### **GET /api/members**

Lista membros.

**Query Params:**
- `cell_id` (opcional): ID da célula
- `status` (opcional): active, inactive
- `page` (opcional): Número da página
- `per_page` (opcional): Itens por página (max 100)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Maria Santos",
      "email": "maria@email.com",
      "phone": "(11) 98765-4321",
      "photo_url": "https://...",
      "cell_id": 1,
      "role_level": 0,
      "baptism_status": "baptized",
      "is_visitor": false
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 150,
    "total_pages": 8
  }
}
```

---

### **GET /api/members/{id}**

Detalhes de um membro.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Maria Santos",
    "email": "maria@email.com",
    "phone": "(11) 98765-4321",
    "photo_url": "https://...",
    "birth_date": "1990-05-15",
    "address": "Rua ...",
    "city": "São Paulo",
    "state": "SP",
    "cell_id": 1,
    "cell_name": "Célula Alpha",
    "role_level": 0,
    "baptism_status": "baptized",
    "baptism_date": "2020-12-25",
    "is_visitor": false,
    "formations": ["CTL", "CME"],
    "total_attendances": 45,
    "attendance_rate": 92.5
  }
}
```

---

### **POST /api/members**

Cria novo membro (apenas líderes+).

**Request:**
```json
{
  "name": "Pedro Costa",
  "email": "pedro@email.com",
  "phone": "(11) 91234-5678",
  "cell_id": 1,
  "baptism_status": "visitor",
  "is_visitor": true
}
```

---

### **PUT /api/members/{id}**

Atualiza membro.

---

## 🏠 **CÉLULAS**

### **GET /api/cells**

Lista células.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Célula Alpha",
      "leader_name": "João Silva",
      "address": "Rua ...",
      "city": "São Paulo",
      "neighborhood": "Centro",
      "latitude": -23.5505,
      "longitude": -46.6333,
      "meeting_day": "friday",
      "meeting_time": "20:00",
      "total_members": 25,
      "active_members": 23,
      "avg_attendance": 85.5
    }
  ]
}
```

---

### **GET /api/cells/{id}**

Detalhes da célula.

---

### **GET /api/cells/map**

Retorna células para exibir no mapa.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Célula Alpha",
      "latitude": -23.5505,
      "longitude": -46.6333,
      "marker_color": "#4A90E2"
    }
  ]
}
```

---

## 📅 **REUNIÕES**

### **GET /api/meetings**

Lista reuniões.

**Query Params:**
- `cell_id` (opcional)
- `date_from` (opcional): Y-m-d
- `date_to` (opcional): Y-m-d
- `status` (opcional): scheduled, completed

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "cell_id": 1,
      "cell_name": "Célula Alpha",
      "meeting_date": "2025-01-15",
      "meeting_time": "20:00",
      "host_name": "Maria Santos",
      "address": "Rua ...",
      "total_members": 25,
      "present_members": 22,
      "attendance_rate": 88.0,
      "status": "completed"
    }
  ]
}
```

---

### **POST /api/meetings**

Cria reunião (apenas líderes+).

**Request:**
```json
{
  "cell_id": 1,
  "meeting_date": "2025-01-20",
  "meeting_time": "20:00",
  "host_name": "Maria Santos",
  "address": "Rua ..."
}
```

---

### **GET /api/meetings/{id}**

Detalhes da reunião com lista de membros.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "cell_name": "Célula Alpha",
    "meeting_date": "2025-01-15",
    "host_name": "Maria Santos",
    "members": [
      {
        "id": 1,
        "name": "Pedro Costa",
        "photo_url": "https://...",
        "is_present": true
      },
      {
        "id": 2,
        "name": "Ana Silva",
        "photo_url": "https://...",
        "is_present": false
      }
    ]
  }
}
```

---

## ✅ **PRESENÇA (CHECKPOINT)**

### **POST /api/attendance**

Registra presença de um membro.

**Request:**
```json
{
  "meeting_id": 1,
  "member_id": 5,
  "is_present": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Presença registrada com sucesso"
}
```

---

### **POST /api/attendance/batch**

Registra múltiplas presenças de uma vez.

**Request:**
```json
{
  "meeting_id": 1,
  "attendances": [
    {"member_id": 1, "is_present": true},
    {"member_id": 2, "is_present": true},
    {"member_id": 3, "is_present": false}
  ]
}
```

---

## 📊 **RELATÓRIOS**

### **GET /api/reports/attendance**

Relatório de presenças.

**Query Params:**
- `date_from`: Y-m-d (obrigatório)
- `date_to`: Y-m-d (obrigatório)
- `cell_id` (opcional)
- `format`: json, pdf, excel

**Response:**
```json
{
  "success": true,
  "data": {
    "period": {
      "start": "2025-01-01",
      "end": "2025-01-31"
    },
    "summary": {
      "total_meetings": 12,
      "total_present": 250,
      "total_absent": 50,
      "avg_attendance_rate": 83.3
    },
    "meetings": [
      {
        "date": "2025-01-15",
        "cell_name": "Célula Alpha",
        "present": 22,
        "absent": 3,
        "rate": 88.0
      }
    ]
  }
}
```

---

### **GET /api/reports/formations**

Relatório de formações.

---

### **GET /api/reports/growth**

Relatório de crescimento.

---

## 🔔 **NOTIFICAÇÕES**

### **GET /api/notifications**

Lista notificações do usuário.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "absence_alert",
      "title": "Membro faltoso",
      "message": "João Silva está com 3 faltas consecutivas",
      "is_read": false,
      "created_at": "2025-01-15 10:30:00"
    }
  ],
  "unread_count": 5
}
```

---

### **PUT /api/notifications/{id}/read**

Marca notificação como lida.

---

## ⚙️ **CONFIGURAÇÕES**

### **GET /api/settings**

Retorna configurações do tenant.

---

### **PUT /api/settings**

Atualiza configurações (apenas admins).

---

## 🔒 **AUTENTICAÇÃO E SEGURANÇA**

### **Headers Obrigatórios:**

```
Authorization: Bearer {token}
Content-Type: application/json
X-Tenant-ID: {tenant_id}
```

### **Rate Limiting:**

- **Geral:** 100 requisições/minuto
- **Login:** 5 tentativas/minuto

### **Códigos HTTP:**

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Internal Server Error

---

## 📱 **EXEMPLO DE USO (JavaScript)**

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('https://api.haklai.com/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.success) {
    localStorage.setItem('token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));
  }
  
  return data;
};

// Listar membros
const getMembers = async (cellId) => {
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user'));
  
  const response = await fetch(`https://api.haklai.com/api/members?cell_id=${cellId}`, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'X-Tenant-ID': user.tenant_id
    }
  });
  
  return await response.json();
};

// Registrar presença
const markAttendance = async (meetingId, memberId, isPresent) => {
  const token = localStorage.getItem('token');
  const user = JSON.parse(localStorage.getItem('user'));
  
  const response = await fetch('https://api.haklai.com/api/attendance', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'X-Tenant-ID': user.tenant_id,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      meeting_id: meetingId,
      member_id: memberId,
      is_present: isPresent
    })
  });
  
  return await response.json();
};
```

---

## 📞 **SUPORTE**

Para dúvidas sobre a API:

📧 Email: api@haklai.com  
📚 Docs: https://docs.haklai.com  

---

Desenvolvido por **Jefter Ruthes** - https://ruthes.dev
