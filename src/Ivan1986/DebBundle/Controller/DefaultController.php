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
    public function packageformAction()
    {
        return array();
    }

    /**
     * @Route("/main_repo", name="main_repo")
     */
    public function mainPackageAction()
    {
        $pkgs = $this->getDoctrine()->getRepository('Ivan1986DebBundle:SysPackage');
        /** @var $pkgs PackageRepository */
        $pkg = $pkgs->getSystem();
        /** @var $pkg SysPackage */
        if (!$pkg)
            throw new NotFoundHttpException();
        return $pkg->getHttpResponse();
    }

}
