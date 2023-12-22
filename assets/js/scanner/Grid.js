Ext.define('GibsonOS.module.obscura.scanner.Grid', {
    extend: 'GibsonOS.module.core.component.grid.Panel',
    alias: ['widget.gosModuleObscuraScannerGrid'],
    multiSelect: false,
    enableDrag: true,
    getShortcuts(records) {
        let shortcuts = [];

        Ext.iterate(records, (record) => {
            shortcuts.push({
                id: null,
                module: 'obscura',
                task: 'scanner',
                action: '',
                text: record.get('deviceName'),
                icon: 'icon_scan',
                parameters: record.getData()
            });
        });

        return shortcuts;
    },
    initComponent(arguments) {
        let me = this;

        me.store = new GibsonOS.module.obscura.store.Scanner();

        me.callParent(arguments);
    },
    enterFunction(record) {
        const formWindow = new GibsonOS.module.core.component.form.Window({
            title: 'Scannen',
            url: baseDir + 'obscura/scanner/form',
            method: 'GET',
            params: {
                deviceName: record.get('deviceName')
            }
        }).show();

        formWindow.down('form').getForm().on('actioncomplete', () => {
            formWindow.close();
        });
    },
    getColumns() {
        return [{
            header: 'Name',
            dataIndex: 'deviceName',
            flex: 1
        },{
            header: 'Hersteller',
            dataIndex: 'vendor',
            width: 100
        },{
            header: 'Modell',
            dataIndex: 'model',
            width: 100
        },{
            header: 'Typ',
            dataIndex: 'type',
            width: 100
        }];
    }
});