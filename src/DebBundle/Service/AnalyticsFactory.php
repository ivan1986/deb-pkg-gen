<?php

namespace Ivan1986\DebBundle\Service;

use FourLabs\GampBundle\Service\AnalyticsFactory as BaseAnalyticsFactory;
use Symfony\Component\HttpFoundation\RequestStack;

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
        $sandbox)
    {
        $analytics = parent::createAnalytics($requestStack, $version, $trackingId, $ssl, $anonymize, $async, $debug, $sandbox);

        $request = $requestStack->getCurrentRequest();
        if (null === $request || !$request->cookies->has('_ga')) {
            $analytics->setClientId(mt_rand(1e10, 1e11 - 1).'.'.mt_rand(1e10, 1e11 - 1));
        }

        return $analytics;
    }
}
