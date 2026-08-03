# Bot Combinadinhos 🤖

Um aplicativo de quadro de tarefas gamificado no Telegram, usando o Google Planilhas como banco de dados e o Google Apps Script como servidor (gratuito!).

## Passo a Passo para Instalação

### 1. Criar o Bot no Telegram
1. Abra o Telegram e procure por `@BotFather`.
2. Envie o comando `/newbot`.
3. Escolha um nome (ex: `Combinadinhos da Família`) e um username (terminando em `bot`, ex: `familiasilvabot`).
4. O BotFather te dará um **TOKEN** (uma string longa). Guarde esse token.

### 2. Criar a Planilha no Google
1. Crie uma nova planilha no Google Sheets.
2. Crie as seguintes abas (o nome tem que ser exato):
   - **Config**: 
     - A1: `Chave`, B1: `Valor`
     - A2: `GROUP_CHAT_ID`, B2: (deixe em branco por enquanto)
   - **Usuarios**:
     - A1: `Nome`, B1: `Papel`, C1: `ID_Telegram`, D1: `Saldo`
     - Cadastre os membros. `Papel` deve ser `Pai` ou `Filho`. Para saber o ID_Telegram, você pode usar um bot como o `@userinfobot`. Deixe `Saldo` como `0`.
   - **Missoes**:
     - A1: `ID`, B1: `Descricao`, C1: `Moedas`, D1: `Dia`
     - O `Dia` pode ser: Segunda, Terça, Quarta, Quinta, Sexta, Sábado, Domingo. Se ficar em branco (ou "Todos"), a missão será enviada todos os dias.
     - Dica: Se você colocar um valor negativo em `Moedas` (ex: `-10`), isso virará uma **Punição/Regra**! O Bot exibirá um botão "Aplicar Punição" exclusivo para os pais.
     - Ex 1: `1`, `Arrumar a cama`, `10`, `Segunda`
     - Ex 2: `2`, `Brigou com o irmão`, `-15`, ``
   - **Lojinha**:
     - A1: `ID`, B1: `Descricao`, C1: `Custo`
     - Ex: `1`, `1 hora de videogame`, `50`
   - **Historico**:
     - A1: `Data`, B1: `Acao`, C1: `Usuario`, D1: `Detalhe`

### 3. Colocar o Código no Apps Script
1. Na sua planilha, clique em **Extensões > Apps Script**.
2. Crie os arquivos `.gs` no editor copiando o conteúdo dos arquivos deste repositório:
   - `main.gs`
   - `telegram_api.gs`
   - `sheets_db.gs`
   - `game_logic.gs`
3. No arquivo `main.gs`, altere a variável `TOKEN` colocando o token que você pegou no BotFather.

### 4. Publicar e Conectar (Webhook)
1. No Apps Script, clique no botão azul **Implantar** (Deploy) no canto superior direito > **Nova Implantação**.
2. Selecione o tipo **App da Web**.
3. Em "Executar como", escolha **Eu** (seu email).
4. Em "Quem tem acesso", escolha **Qualquer pessoa** (Isso é necessário para o Telegram conseguir mandar mensagens para o script).
5. Clique em **Implantar**. Copie a **URL do App da Web** gerada.
6. Volte no arquivo `main.gs`, cole essa URL na variável `WEBHOOK_URL`. Salve o arquivo (Ctrl+S).
7. Ainda no Apps Script, selecione a função `setWebhook` no menu superior (onde fica o botão "Executar") e clique em **Executar**. Dê as permissões necessárias. O Google vai avisar que o script não é seguro, vá em "Avançado > Acessar (inseguro)". Se aparecer "Webhook was set" no log, deu certo!

### 5. Configurar o Gatilho Diário (07:00)
1. No Apps Script, selecione a função `createTimeDrivenTriggers` no menu superior e clique em **Executar**.
2. Isso vai programar o envio automático das tarefas todos os dias pela manhã.

### 6. Configurar o Grupo da Família e Horário
1. Crie um grupo no Telegram e adicione o seu Bot.
2. Mande uma mensagem no grupo: `/entrar`
3. O Bot vai salvar o ID do grupo na planilha.
4. Por padrão, o bot envia as missões às 07:00 da manhã. Você pode alterar esse horário a qualquer momento enviando o comando `/lembrete 08` (para enviar às 08:00, por exemplo).
5. Para testar o envio de tarefas manualmente a qualquer momento, envie `/missoes`.

### 7. Lista Completa de Comandos

**Gerais (Para todos da família):**
- `/start`: Mensagem inicial e de boas-vindas do bot.
- `/missoes`: Mostra a lista de tarefas diárias naquele exato momento, para todos lembrarem o que tem que fazer hoje.
- `/saldo`: Mostra o seu saldo atual de "Combinadinhos".
- `/lojinha`: Abre a loja com todos os benefícios que podem ser trocados pelos pontos.

**Administrativos (Apenas para os Pais):**
- `/entrar`: O bot vincula o grupo atual para começar a enviar as missões diárias ali.
- `/lembrete <hora>`: Configura a hora que o bot mandará as tarefas automaticamente (Ex: `/lembrete 08`).
- `/missoes-add Descrição, Valor, [Dia]`: Cadastra uma nova missão na planilha direto pelo chat. (Ex: `/missoes-add Arrumar a cama, 10, Segunda`)
- `/lojinha-add Descrição, Custo`: Cadastra um novo benefício na lojinha. (Ex: `/lojinha-add 1 hora de videogame, 50`)

Pronto! Seu sistema de tarefas está rodando! 🚀
