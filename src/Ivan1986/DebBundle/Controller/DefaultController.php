<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name);
    }

    /**
     * @Route("/key/{key}")
     * @Template()
     */
    public function getgpgAction($key)
    {
        //"http://keyserver.ubuntu.com:11371/pks/lookup?op=get&search=0x6831CF9528FA7071"
        //"http://keyserver.ubuntu.com:11371/pks/lookup?op=get&search=0x28FA7071"


        $gpg = new \gnupg();
        //$gpg->

        return new Response($key);
    }

}
