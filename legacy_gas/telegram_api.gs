/**
 * Envia uma mensagem de texto simples
 */
function sendMessage(chatId, text, inlineKeyboard = null) {
  const url = `https://api.telegram.org/bot${TOKEN}/sendMessage`;
  
  const payload = {
    chat_id: chatId,
    text: text,
    parse_mode: 'HTML'
  };
  
  if (inlineKeyboard) {
    payload.reply_markup = JSON.stringify({
      inline_keyboard: inlineKeyboard
    });
  }
  
  const options = {
    method: 'post',
    contentType: 'application/json',
    payload: JSON.stringify(payload)
  };
  
  UrlFetchApp.fetch(url, options);
}

/**
 * Edita uma mensagem existente (usado para atualizar os botões)
 */
function editMessageText(chatId, messageId, text, inlineKeyboard = null) {
  const url = `https://api.telegram.org/bot${TOKEN}/editMessageText`;
  
  const payload = {
    chat_id: chatId,
    message_id: messageId,
    text: text,
    parse_mode: 'HTML'
  };
  
  if (inlineKeyboard) {
    payload.reply_markup = JSON.stringify({
      inline_keyboard: inlineKeyboard
    });
  }
  
  const options = {
    method: 'post',
    contentType: 'application/json',
    payload: JSON.stringify(payload)
  };
  
  UrlFetchApp.fetch(url, options);
}

/**
 * Responde a um Callback Query (tira o ícone de "carregando" do botão)
 */
function answerCallbackQuery(callbackQueryId, text = "", showAlert = false) {
  const url = `https://api.telegram.org/bot${TOKEN}/answerCallbackQuery`;
  
  const payload = {
    callback_query_id: callbackQueryId,
    text: text,
    show_alert: showAlert
  };
  
  const options = {
    method: 'post',
    contentType: 'application/json',
    payload: JSON.stringify(payload)
  };
  
  UrlFetchApp.fetch(url, options);
}
