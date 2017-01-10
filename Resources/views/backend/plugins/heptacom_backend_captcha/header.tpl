{block name="backend/base/header/css" append}
    {if $heptacomBackendCaptcha.visibility}
        <link rel="stylesheet" type="text/css" href="{link file='backend/plugins/heptacom_backend_captcha/resources/css/visible.css'}?{Shopware::REVISION}" />
    {else}
        <link rel="stylesheet" type="text/css" href="{link file='backend/plugins/heptacom_backend_captcha/resources/css/invisible.css'}?{Shopware::REVISION}" />
    {/if}
{/block}

{block name="backend/base/header/javascript" append}
    <script src="https://www.google.com/recaptcha/api.js?render=explicit"></script>
{/block}
