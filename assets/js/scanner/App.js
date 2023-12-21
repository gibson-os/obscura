Ext.define('GibsonOS.module.obscura.scanner.App', {
    extend: 'GibsonOS.module.hc.hcSlave.App',
    alias: ['widget.gosModuleObscuraScannerApp'],
    title: 'Neopixel',
    appIcon: 'icon_scan',
    width: 900,
    height: 850,
    requiredPermission: {
        module: 'obscura',
        task: 'scanner'
    },
    initComponent() {
        const me = this;

        me.items = [{
            xtype: 'gosModuleObscuraScannerGrid'
        }];

        me.callParent();
    }
});