<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Ivan1986\DebBundle\Entity\PackageRepository;
use Ivan1986\DebBundle\Entity\Package;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\TwigBundle\TwigEngine;

use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Filesystem;
use Zend\Cache\Storage\Adapter\FilesystemOptions;

use UnitedPrototype\GoogleAnalytics;

/**
 * @Route("/repo")
 * @Template()
 */
class RepoController extends Controller
{
    /** @var Filesystem */
    private $cache;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        $dir = $this->container->getParameter('cache_dir');
        if (!is_dir($dir))
            mkdir($dir, 0777, true);
        $opt = new FilesystemOptions();
        $opt->setCacheDir($dir);
        $opt->setDirPermission(0777);
        $opt->setFilePermission(0666);
        $this->cache = StorageFactory::factory(array(
            'adapter' => 'filesystem',
        ));
        $this->cache->setOptions($opt);
    }


    /**
     * @Route("/", name="repo")
     * @Template()
     */
    public function indexAction()
    {
        return false;
    }

    /**
     * @Route("/dists/{name}/main/{arch}/Packages", name="Packages")
     * @Template()
     */
    public function PackagesAction($name, $arch)
    {
        $key = 'repo_Packages_'.$name;
        $list = $this->cache->getItem($key);
        if (!$list || $name == 'apttest')
        {
            $list = $this->getPkgList($this->getPkgs($name));
            $this->cache->setItem($key, $list);
        }
        $this->get('ivan1986_deb.gapinger')->pingGA('Repository - '.$name);
        $r = new Response($list);
        $r->headers->set('Content-Type', 'application/octet-stream');
        return $r;
    }

    /**
     * @Route("/dists/{name}/Release", name="Release")
     * @Template()
     */
    public function ReleaseAction($name)
    {
        $key = 'repo_Release_'.$name;
        $Release = $this->cache->getItem($key);
        if (!$Release || $name == 'apttest')
        {
            $pkgs = $this->getPkgs($name);
            $Release = $this->getRelease($this->getPkgList($pkgs), $this->getMaxDate($pkgs), $name);
            $this->cache->setItem($key, $Release);
        }

        $r = new Response($Release);
        $r->headers->set('Content-Type', 'application/octet-stream');
        return $r;
    }

    /**
     * @Route("/dists/{name}/Release.gpg", name="ReleaseGpg")
     * @Template()
     */
    public function ReleaseGpgAction($name)
    {
        $key = 'repo_ReleaseGpg_'.$name;
        $ReleaseGpg = $this->cache->getItem($key);
        if (!$ReleaseGpg || $name == 'apttest')
        {
            $pkgs = $this->getPkgs($name);
            $Release = $this->getRelease($this->getPkgList($pkgs), $this->getMaxDate($pkgs), $name);

            $gpg = new \gnupg();
            $content = file_get_contents($this->container->getParameter('key_file'));
            $PrivateKey = $gpg->import($content);
            $gpg->addsignkey($PrivateKey['fingerprint']);
            $gpg->setsignmode(\gnupg::SIG_MODE_DETACH);
            $ReleaseGpg = $gpg->sign($Release);
            $this->cache->setItem($key, $ReleaseGpg);
        }

        $r = new Response($ReleaseGpg);
        $r->headers->set('Content-Type', 'application/octet-stream');
        return $r;
    }

    private function getRelease($list, $date, $name)
    {
        $size = strlen($list);
        $md5 = md5($list);
        $sha1 = sha1($list);
        $sha512 = hash('sha512', $list);

        $templater = $this->get('templating');
        /** @var $templater TwigEngine */
        $Release = $templater->render('Ivan1986DebBundle:Repo:Release.html.twig', array(
            'size' => $size,
            'md5' => $md5,
            'sha1' => $sha1,
            'sha512' => $sha512,
            'date' => date('r', $date),
            'name' => $name,
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
            return time(123456789);
        $date = $packages[0]->getCreated()->getTimestamp();
        foreach($packages as $package)
        {
            /** @var $package Package */
            $date = max($date, $package->getCreated()->getTimestamp());
        }
        return $date;
    }

    /**
     * @param string $repoName Имя репозитория
     * @return \Ivan1986\DebBundle\Entity\Package[]
     */
    private function getPkgs($repoName)
    {
        $rpkgs = $this->getDoctrine()->getRepository('Ivan1986DebBundle:Package');
        /** @var $rpkgs PackageRepository */
        if ($repoName == 'stable')
            return $rpkgs->mainRepo();
        if ($repoName == 'apttest')
            return $rpkgs->testRepo();
        if ($repoName == 'link')
            return $rpkgs->linkRepo();
    }

    private function getPkgList($pkgs)
    {
        if (empty($pkgs))
            return "";
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
        $this->get('ivan1986_deb.gapinger')->pingGA($name);
        return $pkg->getHttpResponse();
    }

}
