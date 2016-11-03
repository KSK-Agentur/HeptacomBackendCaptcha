<?php

class Shopware_Plugins_Backend_HeptacomBackendCaptcha_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    protected $pluginInfo;

    protected function getPluginInfo($key = null, $default = null)
    {
        if (is_null($this->pluginInfo)) {
            $this->pluginInfo = json_decode(file_get_contents(implode(DIRECTORY_SEPARATOR, [__DIR__, 'plugin.json'])), true);
        }

        if (!is_null($key)) {
            if (array_key_exists($key, $this->pluginInfo)) {
                return $this->pluginInfo[$key];
            } else {
                if (!is_null($default)) {
                    return $default;
                } else {
                    throw new Enlight_Exception('Plugin information "' . $key . '" not found.');
                }
            }
        } else {
            return $this->pluginInfo;
        }
    }

    public function getVersion()
    {
        return $this->getPluginInfo('currentVersion');
    }

    public function getLabel()
    {
        return $this->getPluginInfo('label');
    }

    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'supplier' => $this->getPluginInfo('author'),
            'author' => $this->getPluginInfo('author'),
            'description' => $this->getPluginInfo('description', ''),
            'link' => $this->getPluginInfo('link'),
        );
    }

    public function install()
    {
        $compatibility = $this->getPluginInfo('compatibility');
        if (!$this->assertMinimumVersion($compatibility['minimumVersion'])) {
            throw new Enlight_Exception('At least Shopware ' . $compatibility['minimumVersion'] . ' is required');
        }

        $this->createConfiguration();

        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopStartup',
            'onStartDispatch'
        );

        return true;
    }

    public function enable()
    {
        return [
            'success' => true,
            'invalidateCache' => ['template']
        ];
    }

    public function disable()
    {
        return [
            'success' => true,
            'invalidateCache' => ['template']
        ];
    }

    public function uninstall()
    {
        return $this->disable();
    }

    public function update($oldVersion)
    {
        return true;
    }

    public function createConfiguration()
    {
        $form = $this->Form();

        $form->setElement('text', 'sitekey', [
            'label' => 'Websiteschlüssel',
            'description' => 'Verwenden Sie diesen Schlüssel im HTML-Code für die Nutzer Ihrer Website.',
            'required' => true,
            'position' => 1
        ])->setElement('text', 'secret', [
            'label' => 'Geheimer Schlüssel',
            'description' => 'Verwenden Sie diesen Schlüssel für die Kommunikation zwischen Ihrer Website und Google. Halten Sie diesen Schlüssel geheim.',
            'required' => true,
            'position' => 2
        ]);
    }

    public function onStartDispatch(Enlight_Event_EventArgs $args)
    {
        $this->registerMyComponents();

        $subscribers = array(
            new \Shopware\HeptacomBackendCaptcha\Subscriber\Backend()
        );

        foreach ($subscribers as $subscriber) {
            $this->Application()->Events()->addSubscriber($subscriber);
        }
    }

    public function registerMyComponents()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware\HeptacomBackendCaptcha',
            $this->Path()
        );
    }
}
