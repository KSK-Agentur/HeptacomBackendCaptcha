<?php

namespace HeptacomCaptcha;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Enlight_Event_EventArgs;
use Enlight_Controller_Action;
use Enlight_Controller_Request_Request;
use Enlight_Controller_Plugins_ViewRenderer_Bootstrap;
use Shopware\Components\Logger;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use HeptacomCaptcha\Service\CaptchaService;

class HeptacomCaptcha extends Plugin
{
    /**
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_FRONTEND);
    }

    /**
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_FRONTEND);
    }

    /**
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_FRONTEND);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Plugins_ViewRenderer_PreRender' => 'onViewRendererPreRender',
            'Enlight_Controller_Action_PostDispatchSecure_Backend' => 'onBackendPostDispatch',
            'Enlight_Controller_Action_Backend_Login_Login' => 'onBackendLoginLoginAction',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     * @return bool|null
     */
    public function onBackendLoginLoginAction(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $request = $controller->Request();

        /** @var CaptchaService $captchaService */
        $captchaService = $this->container->get('heptacom_captcha.service.captcha_service');

        if ($captchaService->hasKeys()) {
            $gRecaptchaResponse = $request->get('g-recaptcha-response');
            $remoteip = $request->getClientIp();

            if (!$captchaService->evaluate($gRecaptchaResponse, $remoteip)) {
                return false;
            }
        }

        return null;
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onBackendPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();

        try {
            /** @var CaptchaService $captchaService */
            $captchaService = $this->container->get('heptacom_captcha.service.captcha_service');

            if ($captchaService->hasKeys()) {
                $view->assign('heptacomBackendCaptcha', [
                    'visibility' => (bool) Shopware()->Config()->getByNamespace('HeptacomCaptcha', 'visibility'),
                    'sitekey' => $captchaService->getSitekey(),
                ]);

                $view->addTemplateDir(implode(DIRECTORY_SEPARATOR, [$this->getPath(), 'Resources', 'views']));
                $view->extendsTemplate(implode(DIRECTORY_SEPARATOR, [
                    'backend',
                    'plugins',
                    'heptacom_backend_captcha',
                    'header.tpl'
                ]));
                $view->extendsTemplate(implode(DIRECTORY_SEPARATOR, [
                    'backend',
                    'plugins',
                    'heptacom_backend_captcha',
                    'view',
                    'main',
                    'form.js'
                ]));
            }
        }
        catch (ServiceNotFoundException $exception) {
            /**@var Logger $pluginLogger */
            $pluginLogger = $this->container->get('PluginLogger');
            $pluginLogger->error($exception->getMessage());
        }
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onViewRendererPreRender(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Plugins_ViewRenderer_Bootstrap $subject */
        $subject = $args->get('subject');
        $view = $subject->Action()->View();
        /** @var Enlight_Controller_Request_Request $request */
        $request = $args->get('request');
        $module = $request->getModuleName();
        $controller = strtolower(trim($request->getControllerName()));

        if ($module == 'backend'
            && $controller == 'index') {
            $view->Engine()->clearAllCache();
        }
    }
}
