# 🎯 Combinadinhos Bot

Bot do Telegram para gestão de missões e recompensas em família. As crianças completam missões diárias e acumulam pontos (Combinadinhos) que podem ser trocados por recompensas na lojinha.

---

## 📋 Comandos do Bot

### 👶 Comandos para todos os membros

| Comando | Descrição |
|---|---|
| `/start` | Inicia o bot e exibe as boas-vindas com instruções básicas |
| `/missoes` | Lista todas as missões do dia com seus respectivos pontos |
| `/saldo` | Mostra o saldo atual de Combinadinhos do usuário |
| `/lojinha` | Exibe todas as recompensas disponíveis e seus custos em pontos |

### 👨‍👩‍👧 Comandos exclusivos para pais/mães

> **Atenção:** Os comandos abaixo são restritos a usuários com a role `pai` ou `mãe` no banco de dados.

| Comando | Sintaxe | Descrição |
|---|---|---|
| `/missoes-add` | `/missoes-add Descrição, Pontos, [Dia]` | Adiciona uma nova missão. O campo Dia é opcional |
| `/lojinha-add` | `/lojinha-add Descrição, Custo` | Adiciona uma nova recompensa à lojinha |

#### Exemplos de uso

```
/missoes-add Arrumar a cama, 10
/missoes-add Fazer a lição de casa, 20, segunda

/lojinha-add Sorvete, 50
/lojinha-add 1 hora de videogame, 100
```

---

## 🚀 Instalação e Configuração

### Pré-requisitos

- PHP **8.3+**
- Composer
- Node.js e npm
- PostgreSQL (produção) ou SQLite (desenvolvimento)
- Token de um bot do Telegram (obtido via [@BotFather](https://t.me/BotFather))

---

### ⚙️ Configuração Local

#### 1. Clone o repositório

```bash
git clone <url-do-repositorio>
cd combinadinhos
```

#### 2. Instale as dependências

```bash
composer install
npm install
```

#### 3. Configure as variáveis de ambiente

```bash
cp .env.example .env
php artisan key:generate
```

Edite o arquivo `.env` e preencha as variáveis obrigatórias:

```env
# Banco de dados
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=combinadinhos
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# Bot do Telegram
TELEGRAM_BOT_TOKEN="seu_token_aqui"
COMBINADINHOS_GROUP_CHAT_ID="id_do_grupo_aqui"
COMBINADINHOS_REMINDER_TIME="07:00"

# Chave secreta para o cron job
CRON_SECRET="uma_chave_secreta_forte"
```

#### 4. Execute as migrations

```bash
php artisan migrate
```

#### 5. Configure o webhook do Telegram

```bash
php artisan telegraph:set-webhook
```

Ou via rota HTTP (útil para ambientes sem acesso a CLI):

```
GET /api/setup-bot?secret=sua_chave_secreta
```

#### 6. Cadastre o usuário pai/mãe no banco

Insira o seu `telegram_id` na tabela `users` com a role `pai` ou `mãe`. O bot exibe seu ID no comando `/start`.

```sql
INSERT INTO users (name, telegram_id, role, balance)
VALUES ('Seu Nome', 123456789, 'pai', 0);
```

#### 7. Inicie o servidor de desenvolvimento

```bash
composer dev
```

> O comando `composer dev` sobe simultaneamente o servidor PHP, o queue worker, o pail (logs) e o Vite.

---

### 🐳 Deploy com Docker

#### 1. Build da imagem

```bash
docker build -t combinadinhos .
```

#### 2. Execute o container

```bash
docker run -p 8080:8080 \
  -e APP_KEY=sua_app_key \
  -e TELEGRAM_BOT_TOKEN=seu_token \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=seu_host_db \
  -e DB_DATABASE=combinadinhos \
  -e DB_USERNAME=usuario \
  -e DB_PASSWORD=senha \
  -e CRON_SECRET=chave_secreta \
  combinadinhos
```

---

### ☁️ Deploy no Render

O projeto está configurado para deploy no [Render](https://render.com).

1. Conecte o repositório ao Render como **Web Service**
2. Configure as variáveis de ambiente no painel do Render
3. O `Dockerfile` já está configurado e fará o build automaticamente
4. Após o deploy, configure o webhook acessando:

```
https://combinadinhos.onrender.com/api/setup-bot?secret=sua_chave_secreta
```

---

### ⏰ Envio Automático de Missões Diárias

Para que o bot envie as missões automaticamente todo dia, configure um cron job externo (ex: [cron-job.org](https://cron-job.org)) que faça uma requisição GET para:

```
GET https://seu-dominio.com/api/trigger-daily-tasks?secret=sua_chave_secreta
```

Configure o horário desejado na variável `COMBINADINHOS_REMINDER_TIME` do `.env`.

---

## 🗄️ Estrutura do Banco de Dados

| Tabela | Descrição |
|---|---|
| `users` | Membros da família com saldo e role (filho, pai, mãe) |
| `missions` | Missões/tarefas disponíveis com pontuação |
| `rewards` | Recompensas disponíveis na lojinha com custo em pontos |
| `transactions` | Histórico de transações de pontos |

---

## 🛠️ Stack Tecnológica

- **Framework:** Laravel 13
- **Bot Telegram:** [DefStudio Telegraph](https://github.com/defstudio/telegraph)
- **Banco de Dados:** PostgreSQL (produção) / SQLite (desenvolvimento)
- **Deploy:** Docker + Render

---

## 📄 Licença

Este projeto é de uso privado e familiar. ❤️
