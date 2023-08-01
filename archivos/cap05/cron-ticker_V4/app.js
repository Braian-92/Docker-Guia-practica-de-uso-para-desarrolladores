const cron = require('node-cron');
const { syncDB } = require('./tasks/sync-db.js');

console.log('inicio');

cron.schedule('1-59/5 * * * * *', syncDB);

