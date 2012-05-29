<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ivan1986\DebBundle\Entity\PackageRepository;
use Ivan1986\DebBundle\Entity\Package;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @Route("/dists/stable/main/{arch}/Packages", name="Packages")
     * @Template()
     */
    public function PackagesAction($arch)
    {
        $rpkgs = $this->getDoctrine()->getRepository('Ivan1986DebBundle:Package');
        /** @var $rpkgs PackageRepository */
        $pkgs = $rpkgs->findAll();
        $list = array();
        foreach($pkgs as $pkg)
        {
            $info = $pkg->getInfo();
            $info = str_replace('%filename%', 'pool/'.$pkg->getFile(), $info);
            $list[] = $info;
        }
        $list = implode("\n", $list);

        $r = new Response($list);
        $r->headers->set('Content-Type', 'application/octet-stream');
        return $r;
    }

    /**
     * @Route("/pool/{name}", name="Package")
     * @Template()
     */
    public function PoolAction($name)
    {
        $pkgs = $this->getDoctrine()->getRepository('Ivan1986DebBundle:Package');
        /** @var $pkgs PackageRepository */
        $pkg = $pkgs->findOneBy(array('file' => $name));
        /** @var $pkg Package */
        if (!$pkg)
            throw new NotFoundHttpException();
        return $pkg->getHttpResponse();
    }

}
