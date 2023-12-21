Ext.define('GibsonOS.module.obscura.model.Scanner', {
    extend: 'GibsonOS.data.Model',
    idProperty: 'deviceName',
    fields: [{
        name: 'deviceName',
        type: 'string'
    },{
        name: 'vendor',
        type: 'string'
    },{
        name: 'model',
        type: 'string'
    },{
        name: 'type',
        type: 'string'
    }]
});