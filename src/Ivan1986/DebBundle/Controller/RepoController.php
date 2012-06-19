<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ivan1986\DebBundle\Entity\PackageRepository;
use Ivan1986\DebBundle\Entity\Package;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\TwigBundle\TwigEngine;

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
        $list = $this->getPkgList($this->getPkgs());

        $r = new Response($list);
        $r->headers->set('Content-Type', 'application/octet-stream');
        return $r;
    }

    /**
     * @Route("/dists/stable/Release", name="Release")
     * @Template()
     */
    public function ReleaseAction()
    {
        $pkgs = $this->getPkgs();
        $Release = $this->getRelease($this->getPkgList($pkgs), $this->getMaxDate($pkgs));

        $r = new Response($Release);
        $r->headers->set('Content-Type', 'application/octet-stream');
        return $r;
    }

    /**
     * @Route("/dists/stable/Release.gpg", name="ReleaseGpg")
     * @Template()
     */
    public function ReleaseGpgAction()
    {
        $pkgs = $this->getPkgs();
        $Release = $this->getRelease($this->getPkgList($pkgs), $this->getMaxDate($pkgs));

        $gpg = new \gnupg();
        $content = file_get_contents($this->container->getParameter('key_file'));
        $PrivateKey = $gpg->import($content);
        $gpg->addsignkey($PrivateKey['fingerprint']);
        $gpg->setsignmode(\gnupg::SIG_MODE_DETACH);
        $ReleaseGpg = $gpg->sign($Release);

        $r = new Response($ReleaseGpg);
        $r->headers->set('Content-Type', 'application/octet-stream');
        return $r;
    }

    private function getRelease($list, $date)
    {
        $size = strlen($list);
        $md5 = md5($list);
        $sha1 = sha1($list);

        $templater = $this->get('templating');
        /** @var $templater TwigEngine */
        $Release = $templater->render('Ivan1986DebBundle:Repo:Release.html.twig', array(
            'size' => $size,
            'md5' => $md5,
            'sha1' => $sha1,
            'date' => date('r', $date),
        ));
        return $Release;
    }

    /**
     * Возвращает дату последнего пакета
     *
     * @param $packages Список пакетоа
     * @return integer Timestamp
     */
    private function getMaxDate($packages)
    {
        if (empty($packages))
            return time();
        $date = $packages[0]->getCreated()->getTimestamp();
        foreach($packages as $package)
        {
            /** @var $package Package */
            $date = max($date, $package->getCreated()->getTimestamp());
        }
        return $date;
    }

    private function getPkgs()
    {
        $rpkgs = $this->getDoctrine()->getRepository('Ivan1986DebBundle:Package');
        /** @var $rpkgs PackageRepository */
        return $rpkgs->mainRepo();
    }

    private function getPkgList($pkgs)
    {
        $list = array();
        foreach ($pkgs as $pkg) {
            $info = $pkg->getInfo();
            $info = str_replace('%filename%', 'pool/' . $pkg->getFile(), $info);
            $list[] = $info;
        }
        $list = implode("\n", $list);
        return $list;
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
