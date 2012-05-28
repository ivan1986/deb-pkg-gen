<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Ivan1986\DebBundle\Util\Builder;
use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;
use Ivan1986\DebBundle\Entity\Repository;

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
     * @Route("/main_repo")
     * @Template()
     */
    public function mainPackageAction()
    {
        $keys = $this->getDoctrine()->getRepository('Ivan1986DebBundle:GpgKey');
        /** @var $keys GpgKeyRepository */
        $key = $keys->getFromServer($this->container->getParameter('key'), 'keyserver.ubuntu.com');

        $repo = new Repository();
        $repo->setUrl($this->generateUrl('repo', array(), true));
        $repo->setSrc(false);
        $repo->setKey($key);
        $repo->setRelease('stable');
        $repo->setComponents(array('main'));
        $repo->setName($this->container->getParameter('host'));

        $builder = new Builder($this->get('templating'));
        $pkg = $builder->simplePackage($repo);

        return $pkg->getHttpResponse();
    }

}
