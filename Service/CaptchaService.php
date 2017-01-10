<?php

namespace HeptacomBackendCaptcha\Service;

use Shopware\Components\HttpClient\GuzzleFactory;

class CaptchaService
{
    /**
     * @var string
     */
    protected $sitekey;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var GuzzleFactory
     */
    protected $guzzleFactory;

    public function __construct(GuzzleFactory $guzzleFactory)
    {
        $this->guzzleFactory = $guzzleFactory;
        $this->sitekey = Shopware()->Config()->getByNamespace('HeptacomBackendCaptcha', 'sitekey');
        $this->secret = Shopware()->Config()->getByNamespace('HeptacomBackendCaptcha', 'secret');
    }

    /**
     * @return string
     */
    public function getSitekey()
    {
        return $this->sitekey;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * @return GuzzleFactory
     */
    public function getGuzzleFactory()
    {
        return $this->guzzleFactory;
    }

    /**
     * @return bool
     */
    public function hasKeys()
    {
        if (empty($this->sitekey) || empty($this->secret)) {
            return false;
        }

        return true;
    }

    /**
     * @param $gRecaptchaResponse
     * @param $remoteip
     * @return bool
     */
    public function evaluate($gRecaptchaResponse, $remoteip)
    {
        $guzzleClient = $this->guzzleFactory->createClient();

        $response = $guzzleClient->post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $this->secret,
                'response' => $gRecaptchaResponse,
                'remoteip' => $remoteip
            ]
        ])->json();

        return (bool) $response['success'];
    }
}
