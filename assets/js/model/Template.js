Ext.define('GibsonOS.module.obscura.model.Template', {
    extend: 'GibsonOS.data.Model',
    fields: [{
        name: 'id',
        type: 'int'
    },{
        name: 'name',
        type: 'string'
    },{
        name: 'vendor',
        type: 'string'
    },{
        name: 'model',
        type: 'string'
    },{
        name: 'path',
        type: 'string'
    },{
        name: 'filename',
        type: 'string'
    },{
        name: 'multipage',
        type: 'boolean'
    },{
        name: 'format',
        type: 'string'
    },{
        name: 'options',
        type: 'object'
    }]
});