const { syncDB } = require('../../tasks/sync-db.js');

describe('Pruabas en Sync-DB', () => {
    test('Debe de ejecutarse el proceso 2 veces', () => {
        syncDB();
        const times = syncDB();
        console.log('Se llamo', times);
        expect( times ).toBe( 2 );
    });
});