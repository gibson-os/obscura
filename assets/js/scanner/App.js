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
            const templateField = basicForm.findField('name');

            templateField.displayAsValue = true;
            templateField.on('change', (field, value) => {
                let template = field.findRecord('name', value);

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
            setTimeout(function() { me.getStatus() }, 500);
        });
    },
    getStatus(lastCheck = null) {
        const me = this;
        let form = me.down('form');

        me.setLoading(true);

        GibsonOS.Ajax.request({
            url: baseDir + 'obscura/scanner/status',
            params: {
                deviceName: me.params.deviceName,
                lastCheck: lastCheck
            },
            messageBox: {
                buttonHandler(button, response) {
                    const data = Ext.decode(response.responseText).data;

                    if (response.status === 202 && data.extraParameters) {
                        const scanButton = form.down('#buttons').items.findBy((button) => {
                            return button.getXType() === 'button' && button.getText() === 'Scannen';
                        });
                        const oldParameters = scanButton.parameters;
                        scanButton.parameters = Ext.merge(scanButton.parameters, data.extraParameters);
                        scanButton.handler();
                        scanButton.parameters = oldParameters;
                    }
                }
            },
            method: 'GET',
            success(response) {
                const data = Ext.decode(response.responseText).data;

                if (data.locked) {
                    setTimeout(function() { me.getStatus(data.date) }, 500);

                    return;
                }

                me.setLoading(false);
            },
            failure() {
                me.setLoading(false);
            }
        });
    }
});
