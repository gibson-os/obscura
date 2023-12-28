Ext.define('GibsonOS.module.obscura.scanner.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleObscuraScannerApp'],
    title: 'Scannen',
    url: baseDir + 'obscura/scanner/form',
    method: 'GET',
    autoHeight: true,
    y: 50,
    initComponent() {
        const me = this;

        me.params = me.gos.data;
        me.items = [{
            xtype: 'gosCoreComponentFormPanel',
            url: me.url,
            params: me.params,
        }];
        me.title = 'Scannen von ' + me.params.deviceName;

        me.callParent();

        let form = me.down('form');
        let basicForm = form.getForm();
        let setValue = (fieldName, value) => {
            let field = basicForm.findField(fieldName);

            if (field === null) {
                return;
            }

            field.setValue(value);
        };

        form.on('afterAddFields', () => {
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

        basicForm.on('actioncomplete', () => {
            me.setLoading(true);

            let lastCheck = null;
            const reload = function() {
                GibsonOS.Ajax.request({
                    url: baseDir + 'obscura/scanner/status',
                    params: {
                        deviceName: me.params.deviceName,
                        lastCheck: lastCheck
                    },
                    method: 'GET',
                    success: function(response) {
                        const data = Ext.decode(response.responseText).data;
                        lastCheck = data.date;

                        if (data.locked) {
                            setTimeout(reload, 500);

                            return;
                        }

                        me.setLoading(false);
                        lastCheck = null;
                    },
                    failure() {
                        me.setLoading(false);
                        lastCheck = null;
                    }
                });
            };
            setTimeout(reload, 500);
        });
    }
});
