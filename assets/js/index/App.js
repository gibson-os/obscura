Ext.define('GibsonOS.module.obscura.index.App', {
    extend: 'GibsonOS.App',
    alias: ['widget.gosModuleObscuraIndexApp'],
    title: 'Scanner',
    appIcon: 'icon_scan',
    width: 600,
    height: 400,
    requiredPermission: {
        module: 'obscura',
        task: 'index'
    },
    initComponent() {
        const me = this;

        me.items = [{
            xtype: 'gosModuleObscuraScannerGrid'
        }];

        me.callParent();
    }
});