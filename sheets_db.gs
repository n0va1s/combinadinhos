/**
 * Retorna a planilha ativa.
 */
function getDb() {
  return SpreadsheetApp.getActiveSpreadsheet();
}

/**
 * Busca uma configuração na aba "Config"
 */
function getSetting(key) {
  const sheet = getDb().getSheetByName('Config');
  if (!sheet) return null;
  const data = sheet.getDataRange().getValues();
  for (let i = 1; i < data.length; i++) { // Ignora o cabeçalho
    if (data[i][0] == key) return data[i][1];
  }
  return null;
}

/**
 * Define uma configuração na aba "Config"
 */
function setSetting(key, value) {
  const sheet = getDb().getSheetByName('Config');
  if (!sheet) return;
  const data = sheet.getDataRange().getValues();
  for (let i = 1; i < data.length; i++) {
    if (data[i][0] == key) {
      sheet.getRange(i + 1, 2).setValue(value);
      return;
    }
  }
  // Se não existir, adiciona no final
  sheet.appendRow([key, value]);
}

/**
 * Retorna o usuário (linha) se ele for Pai
 */
function isParent(telegramId) {
  const users = getUsers();
  return users.find(u => u.id == telegramId && u.role.toLowerCase() === 'pai') != null;
}

/**
 * Retorna o usuário (linha) se ele for Filho
 */
function isChild(telegramId) {
  const users = getUsers();
  return users.find(u => u.id == telegramId && u.role.toLowerCase() === 'filho');
}

function getUserById(telegramId) {
  const users = getUsers();
  return users.find(u => u.id == telegramId);
}

/**
 * Retorna todos os usuários
 */
function getUsers() {
  const sheet = getDb().getSheetByName('Usuarios');
  const data = sheet.getDataRange().getValues();
  const users = [];
  for (let i = 1; i < data.length; i++) {
    users.push({
      row: i + 1,
      name: data[i][0],
      role: data[i][1],
      id: data[i][2],
      balance: data[i][3]
    });
  }
  return users;
}

function getUserBalance(telegramId) {
  const user = getUserById(telegramId);
  return user ? user.balance : 0;
}

function updateBalance(telegramId, amountToAdd) {
  const user = getUserById(telegramId);
  if (user) {
    const sheet = getDb().getSheetByName('Usuarios');
    const newBalance = Number(user.balance) + Number(amountToAdd);
    sheet.getRange(user.row, 4).setValue(newBalance);
    return newBalance;
  }
  return 0;
}

function getCurrentDayOfWeek() {
  const days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
  return days[new Date().getDay()];
}

function getAllTasks() {
  const sheet = getDb().getSheetByName('Missoes');
  const data = sheet.getDataRange().getValues();
  const tasks = [];
  for (let i = 1; i < data.length; i++) {
    tasks.push({
      id: data[i][0],
      description: data[i][1],
      coins: data[i][2],
      day: data[i][3] ? String(data[i][3]).trim() : ""
    });
  }
  return tasks;
}

function getDailyTasks() {
  const tasks = getAllTasks();
  const today = getCurrentDayOfWeek();
  
  return tasks.filter(task => {
    const day = task.day.toLowerCase();
    // Se o dia estiver em branco, ou for igual ao dia de hoje, ou "todos"
    return day === "" || day === today.toLowerCase() || day === "todos";
  });
}

function getTaskById(taskId) {
  const tasks = getAllTasks();
  return tasks.find(t => t.id == taskId);
}

function getRewards() {
  const sheet = getDb().getSheetByName('Lojinha');
  const data = sheet.getDataRange().getValues();
  const rewards = [];
  for (let i = 1; i < data.length; i++) {
    rewards.push({
      id: data[i][0],
      description: data[i][1],
      cost: data[i][2]
    });
  }
  return rewards;
}

function getRewardById(rewardId) {
  const rewards = getRewards();
  return rewards.find(r => r.id == rewardId);
}

function logAction(date, action, user, detail) {
  const sheet = getDb().getSheetByName('Historico');
  if (sheet) {
    sheet.appendRow([date, action, user, detail]);
  }
}
