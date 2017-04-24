<?php

namespace Ivan1986\DebBundle\Controller;

use Ivan1986\DebBundle\Entity\Package;
use Ivan1986\DebBundle\Repository\PackageRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
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
     * @Method("GET")
     */
    public function indexAction()
    {
        return false;
    }

    /**
     * @Route("/dists/{name}/main/{arch}/Packages", name="Packages")
     * @Method("GET")
     */
    public function packagesAction($name, $arch)
    {
        $list = $this->cache('Packages', $name, function ($name) {
            return $this->getPkgList($this->getPkgs($name));
        });

        $this->get('gamp.analytics')
            ->setEventCategory($name)
            ->setEventAction('Packages')
            ->sendEvent();

        $r = new Response($list);
        $r->headers->set('Content-Type', 'application/octet-stream');

        return $r;
    }

    /**
     * @Route("/dists/{name}/Release", name="Release")
     * @Method("GET")
     */
    public function releaseAction($name)
    {
        $Release = $this->cache('Release', $name, function ($name) {
            $pkgs = $this->getPkgs($name);

            return $this->getRelease($this->getPkgList($pkgs), $this->getMaxDate($pkgs), $name);
        });

        $this->get('gamp.analytics')
            ->setEventCategory($name)
            ->setEventAction('Release')
            ->sendEvent();

        $r = new Response($Release);
        $r->headers->set('Content-Type', 'application/octet-stream');

        return $r;
    }

    /**
     * @Route("/dists/{name}/Release.gpg", name="ReleaseGpg")
     * @Method("GET")
     */
    public function releaseGpgAction($name)
    {
        $ReleaseGpg = $this->cache('ReleaseGpg', $name, function ($name) {
            $pkgs = $this->getPkgs($name);
            $Release = $this->getRelease($this->getPkgList($pkgs), $this->getMaxDate($pkgs), $name);

            $gpg = new \gnupg();
            $content = file_get_contents($this->container->getParameter('key_file'));
            $PrivateKey = $gpg->import($content);
            $gpg->addsignkey($PrivateKey['fingerprint']);
            $gpg->setsignmode(\gnupg::SIG_MODE_DETACH);

            return $gpg->sign($Release);
        });

        $this->get('gamp.analytics')
            ->setEventCategory($name)
            ->setEventAction('Release.gpg')
            ->sendEvent();

        $r = new Response($ReleaseGpg);
        $r->headers->set('Content-Type', 'application/octet-stream');

        return $r;
    }

    /**
     * @Route("/dists/{name}/InRelease", name="InRelease")
     * @Method("GET")
     */
    public function inReleaseAction($name)
    {
        $InRelease = $this->cache('InRelease', $name, function ($name) {
            $pkgs = $this->getPkgs($name);
            $Release = $this->getRelease($this->getPkgList($pkgs), $this->getMaxDate($pkgs), $name);

            $gpg = new \gnupg();
            $content = file_get_contents($this->container->getParameter('key_file'));
            $PrivateKey = $gpg->import($content);
            $gpg->addsignkey($PrivateKey['fingerprint']);
            $gpg->setsignmode(\gnupg::SIG_MODE_CLEAR);

            return $gpg->sign($Release);
        });

        $this->get('gamp.analytics')
            ->setEventCategory($name)
            ->setEventAction('InRelease')
            ->sendEvent();

        return new Response($InRelease);
    }

    private function getRelease($list, $date, $name)
    {
        $size = strlen($list);
        $md5 = md5($list);
        $sha1 = sha1($list);
        $sha512 = hash('sha512', $list);
        $date = (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($date);

        $templater = $this->get('templating');
        /** @var $templater TwigEngine */
        $Release = $templater->render('Ivan1986DebBundle:Repo:Release.html.twig', [
            'size' => $size,
            'md5' => $md5,
            'sha1' => $sha1,
            'sha512' => $sha512,
            'date' => $date->format('D, d M Y H:i:s e'),
            'name' => $name,
        ]);

        return $Release;
    }

    /**
     * Возвращает дату последнего пакета.
     *
     * @param array $packages Список пакетов
     *
     * @return int Timestamp
     */
    private function getMaxDate($packages)
    {
        if (empty($packages)) {
            return time();
        }
        $date = $packages[0]->getCreated()->getTimestamp();
        foreach ($packages as $package) {
            /** @var $package Package */
            $date = max($date, $package->getCreated()->getTimestamp());
        }

        return $date;
    }

    /**
     * @param string $repoName Имя репозитория
     *
     * @return \Ivan1986\DebBundle\Entity\Package[]
     */
    private function getPkgs($repoName)
    {
        $rpkgs = $this->getDoctrine()->getRepository('Ivan1986DebBundle:Package');
        /* @var $rpkgs PackageRepository */
        if ($repoName == 'stable') {
            return $rpkgs->mainRepo();
        }
    }

    private function getPkgList($pkgs)
    {
        if (empty($pkgs)) {
            return '';
        }
        $list = [];
        foreach ($pkgs as $pkg) {
            $info = $pkg->getInfo();
            $info = str_replace('%filename%', 'pool/'.$pkg->getFile(), $info);
            $list[] = $info;
        }
        $list = implode("\n", $list);

        return $list;
    }

    /**
     * @Route("/pool/{name}", name="Package")
     * @Method("GET")
     */
    public function poolAction($name)
    {
        $pkgs = $this->getDoctrine()->getRepository('Ivan1986DebBundle:Package');
        /** @var $pkgs PackageRepository */
        $pkg = $pkgs->findOneBy(['file' => $name]);
        /** @var $pkg Package */
        if (!$pkg) {
            throw new NotFoundHttpException();
        }

        $this->get('gamp.analytics')
            ->setEventCategory('Download')
            ->setEventAction($name)
            ->sendEvent();

        return $pkg->getHttpResponse();
    }

    protected function cache($file, $name, callable $func)
    {
        $key = implode('_', ['repo', $file, $name]);
        if ($name == 'apttest') {
            return $func($name);
        }
        $cache = $this->get('doctrine_cache.providers.repo_cache');
        $data = $cache->fetch($key);
        if (!$data) {
            $data = $func($name);
            $cache->save($key, $data);
        }

        return $data;
    }
}
