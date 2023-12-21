Ext.define('GibsonOS.module.obscura.store.Scanner', {
    extend: 'GibsonOS.data.Store',
    alias: ['hcScannerStore'],
    autoLoad: true,
    pageSize: 100,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'obscura/scanner',
        method: 'GET'
    },
    model: 'GibsonOS.module.obscura.model.Scanner'
});