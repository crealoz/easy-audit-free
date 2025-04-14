<?php

namespace Crealoz\EasyAudit\Model\Config\Source;

use Crealoz\EasyAudit\Service\Config\MiddlewareHost;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Credits extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        private readonly Curl           $curl,
        private readonly MiddlewareHost $middlewareHost,
        private readonly Json                      $json,
                                                   $data = [],
        ?SecureHtmlRenderer $secureRenderer = null,
        ?Random $random = null
    )
    {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data, $secureRenderer, $random);
    }

    public function getElementHtml()
    {
        $hash = $this->middlewareHost->getHash();
        $key = $this->middlewareHost->getKey();
        $this->curl->addHeader('Content-Type', 'application/json');
        $this->curl->addHeader('Authorization', 'Bearer ' . $key . ':' . $hash);
        $this->curl->setOption(CURLOPT_PORT, 8443);
        if ($this->middlewareHost->isSelfSigned()) {
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        }

        $this->curl->post($this->middlewareHost->getHost() . '/api/get-remaining-credit', '');

        $response = $this->json->unserialize($this->curl->getBody());

        $spanColor = 'red';
        if ($response['credits'] > 0 && $response['credits'] < 10) {
            $spanColor = 'orange';
        } elseif ($response['credits'] > 10) {
            $spanColor = 'green';
        }

        return '<span style="color:' . $spanColor . '; font-weight: bold">' . $response['credits'] . '</span> <a href="https://shop.crealoz.fr/shop/credits-for-easyaudit-fixer/" class="action-default" target="_blank">buy credits</a>';
    }

}