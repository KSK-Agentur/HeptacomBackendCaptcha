// invisible captcha
var reCaptchaOnSubmit = function(token) {
    var btn = Ext.getCmp(loginButtonId);
    var win = btn.up('window');
    var formPnl = win.down('form');

    var captchaField = Ext.create('Ext.form.field.Hidden');
    captchaField.name = 'g-recaptcha-response';
    captchaField.setValue(token);
    formPnl.add(captchaField);

    var form = formPnl.getForm();
    var values = form.getValues();

    if(!form.isValid() || !values.password.length || !values.username.length) {
        return false;
    }
    form.submit({
        url: '{url action=login}',
        waitMsg: '{s name=wait/message}Login...{/s}',
        success: function(form, action) {
            window.location.href = window.location.href;
        },
        failure: function(form, action) {
            var lockedUntil, message;
            if(action.result.lockedUntil) {
                action.result.lockedUntil = new Date(action.result.lockedUntil);
                message = "{s name=failure/locked_message}Der Account ist bis zum [lockedUntil:date] um [lockedUntil:date('H:i:s')] Uhr gesperrt.{/s}";
                message = new Ext.Template(message);
                message = message.applyTemplate(action.result);
            } else {
                message = '{s name=failure/input_message}Bitte überprüfen Sie Ihre Eingabe und probieren es erneut.{/s}';
            }
            Ext.Msg.alert('{s name=failure/title}Login fehlgeschlagen{/s}', '{s name=failure/message}Ihr Login war nicht erfolgreich. {/s}' + message);
            return false;
        }
    });
};

Ext.define('Shopware.apps.HeptacomBackendCaptcha.view.main.Form', {
    override:'Shopware.apps.Login.view.main.Form',

    listeners: {
        afterrender: function() {
            window.loginButtonId = this.dockedItems.items[0].items.items[1].id;

            grecaptcha.render(loginButtonId + '-btnEl', {
                'sitekey': '{$heptacomBackendCaptcha.sitekey}',
                'callback' : reCaptchaOnSubmit
            });
        },
        beforeaction: function(subject, action) {
            var formValues = action.form.getFieldValues();

            if (!formValues['g-recaptcha-response']) {
                return false;
            }
        }
    }
});
