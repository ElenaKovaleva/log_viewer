function initContent(){
    Ext.define('LogViewer.model.Record', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'ip'},
            {name: 'client'},
            {name: 'os'},
            {name: 'url_from'},
            {name: 'url_to'},
            {name: 'url_count', type: 'int'}
        ]
    });

    var store = Ext.create('Ext.data.Store', {
        model: 'LogViewer.model.Record',
        proxy: {
            type: 'ajax',
            url: '/log_viewer.php',
            reader: {
                type: 'json',
                rootProperty: 'records',
                totalProperty: 'totalCount'
            }
        },
        pageSize: 10,
        autoLoad: true,
        remoteSort: true,
        remoteFilter: true
    });

    Ext.define('LogViewer.view.Grid', {
        extend: 'Ext.grid.Panel',
        store: store,
        collapsible: true,
        title: 'Статистика просмотров',
        plugins: 'gridfilters',
        enableColumnHide: false,
        forceFit: true,
        viewConfig: {
            stripeRows: true
        },

        columns: [{
            text     : 'IP-адрес',
            sortable : true,
            dataIndex: 'ip',
            filter: {
                type: 'string'
            }
        },{
            text     : 'Браузер',
            sortable : true,
            dataIndex: 'client'
        },{
            text     : 'Операционная система',
            sortable : true,
            dataIndex: 'os'
        },{
            text     : 'URL первого входа',
            width    : 150,
            sortable : false,
            dataIndex: 'url_from'
        },{
            text     : 'Последняя просмотренная страница',
            width    : 150,
            sortable : false,
            dataIndex: 'url_to'
        },{
            text     : 'Количество<br>просмотренных<br>страниц',
            width    : 60,
            sortable : false,
            dataIndex: 'url_count'
        }],
        bbar: Ext.create('Ext.PagingToolbar', {
            store: store,
            displayInfo: true
        })
    });

    Ext.create('Ext.container.Viewport', {
        layout: 'fit',
        items: [Ext.create('LogViewer.view.Grid', {})]
    });
}