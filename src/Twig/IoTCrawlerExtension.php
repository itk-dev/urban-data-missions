<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IoTCrawlerExtension extends AbstractExtension
{
    private $ngsiLdBrokerUrl;

    public function __construct(string $ngsiLdBrokerUrl)
    {
        $this->ngsiLdBrokerUrl = trim($ngsiLdBrokerUrl, '/');
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('iotcrawler_entity_url', [$this, 'entityUrl']),
            new TwigFunction('iotcrawler_subscription_url', [$this, 'subscriptionUrl']),
        ];
    }

    public function entityUrl($value)
    {
        $id = $this->getId($value);

        return $this->ngsiLdBrokerUrl.'/ngsi-ld/v1/entities/'.urlencode((string) $id);
    }

    public function subscriptionUrl($value)
    {
        $id = $this->getId($value);

        return $this->ngsiLdBrokerUrl.'/ngsi-ld/v1/subscriptions/'.urlencode((string) $id);
    }

    private function getId($value)
    {
        if (method_exists($value, 'getId')) {
            return $value->getId();
        } elseif (is_array($value)) {
            return $value['id'];
        }

        return $value;
    }
}
