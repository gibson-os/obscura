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
                action: 'form',
                text: record.get('deviceName'),
                icon: 'icon_scan',
                parameters: {
                    deviceName: record.get('deviceName'),
                    vendor: record.get('vendor'),
                    model: record.get('model'),
                }
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
        let basicForm = form.getForm();
        let setValue = (fieldName, value) => {
            let field = basicForm.findField(fieldName);

            if (field === null) {
                return;
            }

            field.setValue(value);
        };

        form.on('afterAddFields', (field, value) => {
            basicForm.findField('name').on('change', (field, value) => {
                let template = field.findRecordByValue(value);

                if (template === false) {
                    return;
                }

                Ext.iterate(template.getData(), (templateFieldName, templateValue) => {
                    if (templateFieldName === 'name') {
                        return true;
                    }

                    if (templateFieldName === 'options') {
                        Ext.iterate(templateValue, (optionFieldName, optionValue) => {
                            setValue('options[' + optionFieldName + ']', optionValue);
                        });

                        return true;
                    }

                    setValue(templateFieldName, templateValue);
                });
            });
        });

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