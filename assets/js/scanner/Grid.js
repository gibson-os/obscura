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
                deviceName: record.get('deviceName'),
                vendor: record.get('vendor'),
                model: record.get('model'),
            }
        }).show();

        let form = formWindow.down('form');

        form.getForm().on('actioncomplete', () => {
            form.setLoading(true);

            let reload = function() {
                GibsonOS.Ajax.request({
                    url: baseDir + 'obscura/scanner/status',
                    params: {
                        deviceName: record.get('deviceName')
                    },
                    method: 'GET',
                    success(response) {
                        const data = Ext.decode(response.responseText).data;

                        if (data.locked) {
                            setTimeout(reload, 500);

                            return;
                        }

                        form.setLoading(false);
                    },
                    failure() {
                        form.setLoading(false);
                    }
                });
            };
            setTimeout(reload, 500);
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