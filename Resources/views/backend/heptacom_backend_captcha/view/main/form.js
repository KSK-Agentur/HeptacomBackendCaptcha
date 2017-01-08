//{block name="backend/login/view/main/form" append}
Ext.define('Shopware.apps.HeptacomBackendCaptcha.view.main.Form', {
    override:'Shopware.apps.Login.view.main.Form',

    listeners: {
        render: function() {
            window.grecaptcha.render('heptacom_backend_captcha', {
                'sitekey': '{$heptacomBackendCaptcha.sitekey}'
            });
        },
        beforeaction: function(subject, action) {
            action.params['g-recaptcha-response'] = Ext.getElementById('g-recaptcha-response').value;
        }
    },

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        if(Ext.ieVersion === 0 || Ext.ieVersion >= 9) {
            me.captcha = Ext.create('Ext.container.Container', {
                html: '<div id="heptacom_backend_captcha" style="-webkit-transform: scale(0.88);transform: scale(0.88);-webkit-transform-origin: 0 0;transform-origin: 0 0;"></div>'
            });

            me.add(me.captcha);
        }
    }
});

Ext.define('Shopware.apps.HeptacomBackendCaptcha.view.Main', {
    override: 'Shopware.apps.Login.view.Main',
    height: 480
});
//{/block}
