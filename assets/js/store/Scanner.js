Ext.define('GibsonOS.module.obscura.store.Scanner', {
    extend: 'GibsonOS.data.Store',
    alias: ['hcScannerStore'],
    autoLoad: true,
    pageSize: 100,
    proxy: {
        type: 'gosDataProxyAjax',
        url: baseDir + 'obscura/index/scanner',
        method: 'GET'
    },
    model: 'GibsonOS.module.obscura.model.Scanner'
});