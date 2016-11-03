<?php

namespace Shopware\HeptacomBackendCaptcha\Subscriber;

use Enlight\Event\SubscriberInterface;

class Backend implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
        );
    }

    protected function log($msg)
    {
        $msg = '[' . date('d.m.Y H:i:s') . '] ' . $msg . PHP_EOL;
        file_put_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'debug.log']), $msg, FILE_APPEND);
    }
}
