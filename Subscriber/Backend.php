<?php

namespace Shopware\HeptacomBackendCaptcha\Subscriber;

use Enlight\Event\SubscriberInterface;

class Backend implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Action_PostDispatchSecure_Backend' => 'onBackendPostDispatch',
            'Enlight_Controller_Action_Backend_Login_Login' => 'onBackendLoginLoginAction',
        );
    }

    public function onBackendPostDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var $controller \Enlight_Controller_Action */
        $controller = $args->getSubject();
        $view = $controller->View();

        $sitekey = Shopware()->Plugins()->Backend()->HeptacomBackendCaptcha()->Config()->get('sitekey');
        $secret = Shopware()->Plugins()->Backend()->HeptacomBackendCaptcha()->Config()->get('secret');

        if (empty($sitekey) || empty($secret)) {
            return;
        }

        $view->assign('heptacomBackendCaptcha', [
            'sitekey' => $sitekey
        ]);

        $view->addTemplateDir(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Views']));
        $view->extendsTemplate(implode(DIRECTORY_SEPARATOR, ['backend', 'heptacom_backend_captcha', 'header.tpl']));
        $view->extendsTemplate(implode(DIRECTORY_SEPARATOR, ['backend', 'heptacom_backend_captcha', 'view', 'main', 'form.js']));
    }

    public function onBackendLoginLoginAction(\Enlight_Event_EventArgs $args)
    {
        /** @var $controller \Enlight_Controller_Action */
        $controller = $args->getSubject();
        $request = $controller->Request();

        $sitekey = Shopware()->Plugins()->Backend()->HeptacomBackendCaptcha()->Config()->get('sitekey');
        $secret = Shopware()->Plugins()->Backend()->HeptacomBackendCaptcha()->Config()->get('secret');

        if (empty($sitekey) || empty($secret)) {
            return;
        }

        $gRecaptchaResponse = $request->get('g-recaptcha-response');
        $remoteip = $request->getClientIp();

        if (!$this->evaluateCaptcha($secret, $gRecaptchaResponse, $remoteip)) {
            return false;
        }
    }

    protected function evaluateCaptcha($secret, $gRecaptchaResponse, $remoteip)
    {
        $guzzleFactory = Shopware()->Container()->get('guzzle_http_client_factory');
        $guzzleClient = $guzzleFactory->createClient();

        $response = $guzzleClient->post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret,
                'response' => $gRecaptchaResponse,
                'remoteip' => $remoteip
            ]
        ]);
        $responseJson = $response->json();

        return (bool) $responseJson['success'];
    }

    protected function log($msg)
    {
        $msg = '[' . date('d.m.Y H:i:s') . '] ' . $msg . PHP_EOL;
        file_put_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'debug.log']), $msg, FILE_APPEND);
    }
}
