/**
 * Envia as missões diárias para o grupo
 */
function sendDailyTasksToGroup(chatId) {
  const tasks = getDailyTasks();
  if (tasks.length === 0) {
    sendMessage(chatId, "🎉 Não há missões cadastradas para hoje!");
    return;
  }
  
  sendMessage(chatId, "☀️ Bom dia família! Aqui estão as missões e regras de hoje:");
  
  tasks.forEach(task => {
    let text = "";
    let keyboard = [];
    
    if (task.coins < 0) {
      // É uma punição/regra negativa
      text = `🚨 <b>Regra:</b> ${task.description}\n📉 <b>Penalidade:</b> ${task.coins} Combinadinhos`;
      keyboard = [
        [{ text: "🚨 Aplicar Punição (Pais)", callback_data: `PUNISH_SELECT_${task.id}` }]
      ];
    } else {
      // Missão normal
      text = `🎯 <b>Missão:</b> ${task.description}\n💎 <b>Recompensa:</b> +${task.coins} Combinadinhos`;
      keyboard = [
        [{ text: "✅ Concluir Missão", callback_data: `DONE_${task.id}` }]
      ];
    }
    sendMessage(chatId, text, keyboard);
  });
}

/**
 * Quando um filho clica em "Concluir Missão"
 */
function processTaskDone(chatId, messageId, taskId, userId, userName, callbackQueryId) {
  const child = isChild(userId);
  if (!child) {
    answerCallbackQuery(callbackQueryId, "Apenas os filhos podem concluir as missões!", true);
    return;
  }
  
  const task = getTaskById(taskId);
  if (!task) {
    answerCallbackQuery(callbackQueryId, "Missão não encontrada.", true);
    return;
  }
  
  answerCallbackQuery(callbackQueryId, "Missão marcada como concluída! Aguardando aprovação.");
  
  // Atualiza a mensagem para mostrar que está aguardando os pais
  const text = `🎯 <b>Missão:</b> ${task.description}\n💎 <b>Recompensa:</b> ${task.coins} Combinadinhos\n\n⏳ <b>${child.name}</b> concluiu a missão! Aguardando aprovação dos pais.`;
  
  const keyboard = [
    [{ text: "👍 Aprovar (Pais)", callback_data: `APPROVE_${task.id}_${child.id}` }]
  ];
  
  editMessageText(chatId, messageId, text, keyboard);
}

/**
 * Quando um pai clica em "Aprovar"
 */
function processTaskApprove(chatId, messageId, taskId, childId, parentTelegramId, callbackQueryId) {
  if (!isParent(parentTelegramId)) {
    answerCallbackQuery(callbackQueryId, "Apenas os pais podem aprovar as missões!", true);
    return;
  }
  
  const task = getTaskById(taskId);
  const child = getUserById(childId);
  
  if (!task || !child) {
    answerCallbackQuery(callbackQueryId, "Erro ao encontrar missão ou filho.", true);
    return;
  }
  
  // Aprova a missão: adiciona saldo
  const newBalance = updateBalance(child.id, task.coins);
  
  // Log no histórico
  const now = new Date();
  logAction(now.toLocaleString(), 'Missão Concluída', child.name, `${task.description} (+${task.coins} Combinadinhos)`);
  
  answerCallbackQuery(callbackQueryId, "Missão aprovada com sucesso!", false);
  
  // Atualiza a mensagem da missão
  const text = `🎯 <b>Missão:</b> ${task.description}\n✅ Concluída e Aprovada!\n🎉 <b>${child.name}</b> ganhou ${task.coins} Combinadinhos e agora tem ${newBalance} no total!`;
  editMessageText(chatId, messageId, text); // Sem botões
}

/**
 * Mostra a lojinha
 */
function showStore(chatId) {
  const rewards = getRewards();
  if (rewards.length === 0) {
    sendMessage(chatId, "A lojinha está vazia no momento.");
    return;
  }
  
  sendMessage(chatId, "🛒 <b>Lojinha de Recompensas</b>\nEscolha o que deseja comprar:");
  
  rewards.forEach(reward => {
    const text = `🎁 ${reward.description}\n💰 <b>Preço:</b> ${reward.cost} Combinadinhos`;
    const keyboard = [
      [{ text: `Comprar (${reward.cost})`, callback_data: `BUY_${reward.id}` }]
    ];
    sendMessage(chatId, text, keyboard);
  });
}

/**
 * Processa a compra de uma recompensa
 */
function processBuyReward(chatId, messageId, rewardId, userId, callbackQueryId) {
  const child = isChild(userId);
  if (!child) {
    answerCallbackQuery(callbackQueryId, "Apenas os filhos podem comprar recompensas!", true);
    return;
  }
  
  const reward = getRewardById(rewardId);
  if (!reward) {
    answerCallbackQuery(callbackQueryId, "Recompensa não encontrada.", true);
    return;
  }
  
  if (child.balance < reward.cost) {
    answerCallbackQuery(callbackQueryId, `Você não tem Combinadinhos suficientes! Faltam ${reward.cost - child.balance}.`, true);
    return;
  }
  
  // Debita do saldo
  const newBalance = updateBalance(userId, -reward.cost);
  
  // Log
  const now = new Date();
  logAction(now.toLocaleString(), 'Recompensa Resgatada', child.name, `${reward.description} (-${reward.cost} Combinadinhos)`);
  
  answerCallbackQuery(callbackQueryId, "Compra realizada com sucesso!", false);
  
  const text = `🎁 <b>Recompensa Comprada!</b>\n\n<b>${child.name}</b> acabou de comprar:\n🛒 <b>${reward.description}</b> por ${reward.cost} Combinadinhos.\n\nSaldo atual: ${newBalance} Combinadinhos.`;
  sendMessage(chatId, text);
  
  // Opcional: Atualizar a mensagem original da loja (remover o botão daquele item, mas pode atrapalhar se outros filhos quiserem comprar, então vamos deixar)
}

/**
 * Quando um pai clica em "Aplicar Punição"
 */
function processPunishSelect(chatId, messageId, taskId, parentTelegramId, callbackQueryId) {
  if (!isParent(parentTelegramId)) {
    answerCallbackQuery(callbackQueryId, "Apenas os pais podem aplicar punições!", true);
    return;
  }
  
  const task = getTaskById(taskId);
  if (!task) {
    answerCallbackQuery(callbackQueryId, "Regra não encontrada.", true);
    return;
  }
  
  const users = getUsers();
  const children = users.filter(u => u.role.toLowerCase() === 'filho');
  
  if (children.length === 0) {
    answerCallbackQuery(callbackQueryId, "Nenhum filho cadastrado na planilha.", true);
    return;
  }
  
  answerCallbackQuery(callbackQueryId, "Selecione o filho.");
  
  const text = `🚨 <b>Regra Quebrada:</b> ${task.description}\n📉 <b>Penalidade:</b> ${task.coins} Combinadinhos\n\n👇 Quem cometeu a infração?`;
  
  // Cria um botão para cada filho
  const keyboard = children.map(child => {
    return [{ text: child.name, callback_data: `PUNISH_APPLY_${task.id}_${child.id}` }];
  });
  
  editMessageText(chatId, messageId, text, keyboard);
}

/**
 * Quando um pai seleciona o filho para aplicar a punição
 */
function processPunishApply(chatId, messageId, taskId, childId, parentTelegramId, callbackQueryId) {
  if (!isParent(parentTelegramId)) {
    answerCallbackQuery(callbackQueryId, "Apenas os pais podem aplicar punições!", true);
    return;
  }
  
  const task = getTaskById(taskId);
  const child = getUserById(childId);
  
  if (!task || !child) {
    answerCallbackQuery(callbackQueryId, "Erro ao encontrar regra ou filho.", true);
    return;
  }
  
  // Aplica a punição (task.coins já é negativo, então updateBalance vai subtrair)
  const newBalance = updateBalance(child.id, task.coins);
  
  const now = new Date();
  logAction(now.toLocaleString(), 'Punição Aplicada', child.name, `${task.description} (${task.coins} Combinadinhos)`);
  
  answerCallbackQuery(callbackQueryId, "Punição aplicada com sucesso!", false);
  
  const text = `🚨 <b>Regra Quebrada:</b> ${task.description}\n❌ Punição aplicada a <b>${child.name}</b>!\n📉 O saldo de ${child.name} foi reduzido em ${Math.abs(task.coins)} e agora é ${newBalance} Combinadinhos.`;
  
  editMessageText(chatId, messageId, text); // Remove os botões
}
