<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;

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
     * @Route("/")
     * @Template()
     */
    public function packageformAction()
    {
        return array();
    }

    /**
     * @Route("/key/{key}")
     * @Template()
     */
    public function getgpgAction($key)
    {
        //"http://keyserver.ubuntu.com:11371/pks/lookup?op=get&search=0x6831CF9528FA7071"
        //"http://keyserver.ubuntu.com:11371/pks/lookup?op=get&search=0x28FA7071"
        $keys = $this->getDoctrine()->getRepository('Ivan1986DebBundle:GpgKey');
        /** @var $keys GpgKeyRepository */
        $key = $keys->getFromServer('28FA7071', 'keyserver.ubuntu.com');

        return new Response($key);
    }

}
