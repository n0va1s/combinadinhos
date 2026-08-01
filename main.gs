const TOKEN = 'SEU_TOKEN_DO_TELEGRAM_AQUI'; // Substitua pelo token do BotFather
const WEBHOOK_URL = 'URL_DO_SEU_WEBAPP_AQUI'; // Substitua pela URL gerada ao Implantar o script

/**
 * Função chamada pelo Telegram toda vez que alguém manda mensagem ou clica num botão
 */
function doPost(e) {
  try {
    const update = JSON.parse(e.postData.contents);
    
    // Filtro anti-repetição (Evita que o Telegram reenvie mensagens por causa do erro 302)
    const cache = CacheService.getScriptCache();
    if (update.update_id && cache.get(update.update_id.toString())) {
      return ContentService.createTextOutput("OK"); // Já processamos, ignora
    }
    if (update.update_id) {
      cache.put(update.update_id.toString(), "1", 3600); // Salva no cache por 1 hora
    }
    
    // Se for um clique num botão (Callback Query)
    if (update.callback_query) {
      handleCallback(update.callback_query);
    }
    // Se for uma mensagem de texto (Comando)
    else if (update.message && update.message.text) {
      handleMessage(update.message);
    }
    
    return ContentService.createTextOutput("OK");
  } catch (error) {
    console.error(error);
    
    // Tenta enviar a mensagem de erro para o Telegram para facilitar o debug
    try {
      const update = JSON.parse(e.postData.contents);
      const chatId = update.message ? update.message.chat.id : (update.callback_query ? update.callback_query.message.chat.id : null);
      if (chatId) {
        sendMessage(chatId, "❌ Erro na Planilha: " + error.message + "\n\nVerifique se o nome das abas estão exatos (sem acento): Config, Usuarios, Missoes, Lojinha, Historico.");
      }
    } catch(e2) {}
    
    return ContentService.createTextOutput("Error");
  }
}

/**
 * Configura o Webhook do Telegram (Rodar manualmente uma vez após implantar)
 */
function setWebhook() {
  const url = `https://api.telegram.org/bot${TOKEN}/setWebhook?url=${WEBHOOK_URL}`;
  const response = UrlFetchApp.fetch(url);
  Logger.log(response.getContentText());
}

function resetWebhook() {
  // Limpa a fila de mensagens travadas no Telegram
  UrlFetchApp.fetch(`https://api.telegram.org/bot${TOKEN}/deleteWebhook?drop_pending_updates=true`);
  // Refaz o webhook
  const url = `https://api.telegram.org/bot${TOKEN}/setWebhook?url=${WEBHOOK_URL}`;
  const response = UrlFetchApp.fetch(url);
  Logger.log("Fila limpa e Webhook resetado: " + response.getContentText());
}

/**
 * Roteia as mensagens de texto/comandos
 */
function handleMessage(message) {
  const text = message.text;
  const chatId = message.chat.id;
  const userId = message.from.id;

  if (text.startsWith('/start')) {
    sendMessage(chatId, "Olá! Sou o bot dos Combinadinhos. Para iniciar o dia, os pais podem usar o comando /missoes.\nPara salvar este grupo, use o comando /entrar");
  } else if (text.startsWith('/missoes-add')) {
    if (isParent(userId)) {
      const content = text.replace('/missoes-add', '').trim();
      if (!content) {
        sendMessage(chatId, "Uso: /missoes-add Descrição, Valor, [Dia]\nEx: /missoes-add Arrumar a cama, 10, Segunda\nEx: /missoes-add Estudar, 15");
        return;
      }
      const parts = content.split(',').map(s => s.trim());
      const description = parts[0];
      const coins = parseInt(parts[1], 10);
      const day = parts[2] || "";
      
      if (!description || isNaN(coins)) {
        sendMessage(chatId, "Erro de formato. Certifique-se de enviar a descrição e o valor separados por vírgula.");
        return;
      }
      
      addMission(description, coins, day);
      sendMessage(chatId, `✅ Missão adicionada com sucesso!\n🎯 ${description} (${coins} Combinadinhos)`);
    } else {
      sendMessage(chatId, `Apenas pais podem adicionar missões! (Seu ID do Telegram é: ${userId})`);
    }
  } else if (text.startsWith('/missoes')) {
    // Apenas pais podem disparar manualmente
    if (isParent(userId)) {
      sendDailyTasksToGroup(chatId);
    } else {
      sendMessage(chatId, `Apenas os pais podem enviar as tarefas manuais! (Seu ID: ${userId})`);
    }
  } else if (text.startsWith('/saldo')) {
    const balance = getUserBalance(userId);
    sendMessage(chatId, `Seu saldo atual é de ${balance} Combinadinhos!`);
  } else if (text.startsWith('/lojinha-add')) {
    if (isParent(userId)) {
      const content = text.replace('/lojinha-add', '').trim();
      if (!content) {
        sendMessage(chatId, "Uso: /lojinha-add Descrição, Custo\nEx: /lojinha-add 1 hora de videogame, 50");
        return;
      }
      const parts = content.split(',').map(s => s.trim());
      const description = parts[0];
      const cost = parseInt(parts[1], 10);
      
      if (!description || isNaN(cost)) {
        sendMessage(chatId, "Erro de formato. Certifique-se de enviar a descrição e o custo separados por vírgula.");
        return;
      }
      
      addReward(description, cost);
      sendMessage(chatId, `✅ Recompensa adicionada com sucesso!\n🎁 ${description} (Custa: ${cost})`);
    } else {
      sendMessage(chatId, `Apenas os pais podem adicionar recompensas! (Seu ID: ${userId})`);
    }
  } else if (text.startsWith('/lojinha')) {
    showStore(chatId);
  } else if (text.startsWith('/entrar')) {
    if (isParent(userId)) {
      setSetting('GROUP_CHAT_ID', chatId);
      sendMessage(chatId, "Grupo configurado com sucesso! As tarefas diárias serão enviadas aqui no horário configurado.");
    } else {
      sendMessage(chatId, `Apenas pais podem configurar o grupo. (Seu ID do Telegram: ${userId})`);
    }
  } else if (text.startsWith('/lembrete')) {
    if (isParent(userId)) {
      const parts = text.split(' ');
      if (parts.length < 2) {
        sendMessage(chatId, "Uso incorreto. Exemplo de uso: /lembrete 08");
        return;
      }
      
      let hourStr = parts[1];
      // Se mandar algo como "08:00", pega só o "08"
      if (hourStr.includes(':')) {
        hourStr = hourStr.split(':')[0];
      }
      
      const hour = parseInt(hourStr, 10);
      if (isNaN(hour) || hour < 0 || hour > 23) {
        sendMessage(chatId, "Hora inválida. Use um número de 0 a 23 (ex: /lembrete 08).");
        return;
      }
      
      // Salva na planilha
      setSetting('DAILY_TASK_HOUR', hour);
      
      // Recria os gatilhos com a nova hora
      createTimeDrivenTriggers(hour);
      
      sendMessage(chatId, `⏰ Horário automático configurado com sucesso! As missões serão enviadas todos os dias entre ${hour}:00 e ${hour}:59.`);
    } else {
      sendMessage(chatId, "Apenas pais podem configurar o horário.");
    }
  }
}

/**
 * Roteia os cliques nos botões
 */
function handleCallback(callbackQuery) {
  const data = callbackQuery.data; // Ex: "DONE_tarefa1", "APPROVE_tarefa1_user123", "BUY_reward1"
  const chatId = callbackQuery.message.chat.id;
  const messageId = callbackQuery.message.message_id;
  const userId = callbackQuery.from.id;
  const userName = callbackQuery.from.first_name;

  if (data.startsWith('DONE_')) {
    const taskId = data.split('_')[1];
    processTaskDone(chatId, messageId, taskId, userId, userName, callbackQuery.id);
  } else if (data.startsWith('APPROVE_')) {
    const parts = data.split('_');
    const taskId = parts[1];
    const childId = parts[2];
    processTaskApprove(chatId, messageId, taskId, childId, userId, callbackQuery.id);
  } else if (data.startsWith('BUY_')) {
    const rewardId = data.split('_')[1];
    processBuyReward(chatId, messageId, rewardId, userId, callbackQuery.id);
  } else if (data.startsWith('PUNISH_SELECT_')) {
    const taskId = data.split('PUNISH_SELECT_')[1];
    processPunishSelect(chatId, messageId, taskId, userId, callbackQuery.id);
  } else if (data.startsWith('PUNISH_APPLY_')) {
    const parts = data.split('PUNISH_APPLY_')[1].split('_'); // [taskId, childId]
    const taskId = parts[0];
    const childId = parts[1];
    processPunishApply(chatId, messageId, taskId, childId, userId, callbackQuery.id);
  }
}

/**
 * Função para ser agendada todos os dias às 07:00
 */
function sendDailyTasksAutomatic() {
  // Pegar o ID do grupo na planilha (Aba Configurações)
  const groupId = getSetting('GROUP_CHAT_ID');
  if (groupId) {
    sendDailyTasksToGroup(groupId);
  } else {
    Logger.log("ID do grupo não configurado.");
  }
}

/**
 * Cria o gatilho (trigger) diário.
 * Pode ser chamado manualmente ou via comando /config_horario.
 */
function createTimeDrivenTriggers(hourArg = null) {
  let hour = hourArg;
  if (hour === null) {
    const saved = getSetting('DAILY_TASK_HOUR');
    hour = saved !== null ? parseInt(saved, 10) : 7;
  }
  
  // Apaga gatilhos antigos do sendDailyTasksAutomatic
  const triggers = ScriptApp.getProjectTriggers();
  for (let i = 0; i < triggers.length; i++) {
    if (triggers[i].getHandlerFunction() === 'sendDailyTasksAutomatic') {
      ScriptApp.deleteTrigger(triggers[i]);
    }
  }
  
  // Cria gatilho para rodar no horário configurado
  ScriptApp.newTrigger('sendDailyTasksAutomatic')
      .timeBased()
      .atHour(hour)
      .nearMinute(0)
      .everyDays(1)
      .create();
      
  Logger.log(`Gatilho diário configurado para a faixa das ${hour}:00`);
}
