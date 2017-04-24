<?php

namespace Ivan1986\DebBundle\Controller;

use Ivan1986\DebBundle\Entity\SysPackage;
use Ivan1986\DebBundle\Repository\PackageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/repo-self.deb", name="main_repo")
     * @Method("GET")
     */
    public function mainPackageAction()
    {
        return $this->getSysPkg('self', 'Main Package');
    }

    private function getSysPkg($name, $GAname)
    {
        $pkgs = $this->getDoctrine()->getRepository('Ivan1986DebBundle:SysPackage');
        /** @var $pkgs PackageRepository */
        $pkg = $pkgs->getSystem();
        $p = false;
        foreach ($pkg as $p) {
            /** @var $p SysPackage */
            if (strpos($p->getFile(), $name) !== false) {
                break;
            }
        }
        $pkg = $p;
        /** @var $pkg SysPackage */
        if (!$pkg) {
            throw new NotFoundHttpException();
        }

        $this->get('gamp.analytics')
            ->setEventCategory('Download')
            ->setEventAction($GAname)
            ->sendEvent();

        return $pkg->getHttpResponse();
    }
}
