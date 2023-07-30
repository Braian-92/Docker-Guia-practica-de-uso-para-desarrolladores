const cron = require('node-cron');

let times = 0;
cron.schedule('1-59/5 * * * * *', () => {
    times++;
    console.log('tick cada 5 segundos', times);
});

console.log('inicio');