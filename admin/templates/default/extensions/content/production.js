Toc.ProductionDashboard = function (config) {
    var that = this;
    config = config || {};
    config.region = 'center';
    config.title = 'Surveillance';
    config.layout = 'form';
    config.loadMask = false;
    config.autoScroll = true;
    config.listeners = {
        activate: function (panel) {
            //console.log('activate');
            this.buildItems();
        },
        deactivate: function (panel) {
            //console.log('deactivate');
            //this.onStop();
        },
        scope: this
    };

    Toc.ProductionDashboard.superclass.constructor.call(this, config);
};

Ext.extend(Toc.ProductionDashboard, Ext.Panel, {

    buildItems: function () {
        if (this.items) {
            this.removeAll(true);
        }

        this.panels = [];

        //this.getEl().mask('Chargement');
        var panel = new Toc.GoldenGateDashboardPanel({owner: this,isProduction : true});
        this.add(panel);
        this.panels[0] = panel;
        this.doLayout();

        var panel1 = new Toc.databaseSpaceDashboard({owner: this,isProduction : true});
        this.add(panel1);
        this.panels[1] = panel1;
        this.doLayout();
    }
});