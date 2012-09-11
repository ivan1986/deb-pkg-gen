<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Ivan1986\DebBundle\Util\Builder;
use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;
use Ivan1986\DebBundle\Entity\SysPackage;
use Ivan1986\DebBundle\Entity\PackageRepository;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="home")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/repo-self.deb", name="main_repo")
     */
    public function mainPackageAction()
    {
        return $this->getSysPkg('main', 'Main Package');
    }

    /**
     * @Route("/repo-link.deb", name="link_repo")
     */
    public function linkPackageAction()
    {
        return $this->getSysPkg('link', 'Link Package');
    }

    private function getSysPkg($name, $GAname)
    {
        $pkgs = $this->getDoctrine()->getRepository('Ivan1986DebBundle:SysPackage');
        /** @var $pkgs PackageRepository */
        $pkg = $pkgs->getSystem();
        $p = false;
        foreach($pkg as $p)
        {
            /** @var $p SysPackage */
            if (strpos($p->getFile(), $name) !== false)
                break;
        }
        $pkg = $p;
        /** @var $pkg SysPackage */
        if (!$pkg)
            throw new NotFoundHttpException();
        $this->get('ivan1986_deb.gapinger')->pingGA($GAname);
        return $pkg->getHttpResponse();
    }

}
