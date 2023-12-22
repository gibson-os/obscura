Ext.define('GibsonOS.module.obscura.scanner.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleObscuraScannerApp'],
    title: 'Scanner',
    appIcon: 'icon_scan',
    width: 600,
    height: 400,
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