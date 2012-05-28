<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/repo")
 * @Template()
 */
class RepoController extends Controller
{

    /**
     * @Route("/", name="repo")
     * @Template()
     */
    public function indexAction()
    {
        return false;
    }

    /**
     * @Route("/dists/stable/main/binary-i386/Packages", name="Packages")
     * @Template()
     */
    public function PackagesAction()
    {
        return new Response('');
    }

}
