<?php

namespace Ivan1986\DebBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use FourLabs\GampBundle\Service\AnalyticsFactory as BaseAnalyticsFactory;

class AnalyticsFactory extends BaseAnalyticsFactory
{
    public function createAnalytics(
        RequestStack $requestStack,
        $version,
        $trackingId,
        $ssl,
        $anonymize,
        $async,
        $debug,
        $enabled)
    {
        $analytics = parent::createAnalytics($requestStack, $version, $trackingId, $ssl, $anonymize, $async, $debug, $enabled);

        $request = $requestStack->getCurrentRequest();
        if (is_null($request) || !$request->cookies->has('_ga')) {
            $analytics->setClientId(mt_rand(1e10, 1e11 - 1) . '.' . mt_rand(1e10, 1e11 - 1));
        }

        return $analytics;
    }

}
