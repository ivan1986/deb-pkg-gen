<?php

namespace Ivan1986\DebBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use UnitedPrototype\GoogleAnalytics;

class GAPinger
{
    /** @var ContainerInterface */
    private $c;

    public function __construct(ContainerInterface $c)
    {
        $this->c = $c;
    }

    public function pingGA($title)
    {
        // Initilize GA Tracker
        $acc = $this->c->getParameter('gaAcc');
        $acc = str_replace('UA', 'MO', $acc);
        $request = $this->c->get('request_stack')->getCurrentRequest();
        $tracker = new GoogleAnalytics\Tracker($acc, 'pkggen.no-ip.org');

        // Assemble Visitor information
        // (could also get unserialized from database)
        $visitor = new GoogleAnalytics\Visitor();
        $visitor->setIpAddress($request->getClientIp());
        $visitor->setUserAgent($request->server->get('HTTP_USER_AGENT'));
        $visitor->setScreenResolution('80x25');

        // Assemble Session information
        // (could also get unserialized from PHP session)
        $session = new GoogleAnalytics\Session();

        // Assemble Page information
        $page = new GoogleAnalytics\Page($request->getRequestUri());
        $page->setTitle($title);

        // Track page view
        $tracker->trackPageview($page, $session, $visitor);
    }
}
