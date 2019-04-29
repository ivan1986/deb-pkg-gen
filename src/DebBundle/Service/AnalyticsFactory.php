<?php

namespace Ivan1986\DebBundle\Service;

use FourLabs\GampBundle\Service\AnalyticsFactory as BaseAnalyticsFactory;

class AnalyticsFactory extends BaseAnalyticsFactory
{
    public function createAnalytics() {
        $analytics = parent::createAnalytics();

        if (!$analytics->getClientId()) {
            $analytics->setClientId(random_int(1e10, 1e11 - 1).'.'.random_int(1e10, 1e11 - 1));
        }

        return $analytics;
    }
}
