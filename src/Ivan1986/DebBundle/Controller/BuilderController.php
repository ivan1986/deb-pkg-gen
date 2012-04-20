<?php

namespace Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;

use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Ivan1986\DebBundle\Entity\RepositoryRepository;

class BuilderController extends Controller
{
    /** @var string Путь до пакетов */
    private $path;

    public function __construct()
    {
        $this->path = dirname(__DIR__).'/package';
    }

    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $keys = $this->getDoctrine()->getRepository('Ivan1986DebBundle:GpgKey');
        /** @var $keys GpgKeyRepository */
        $key = $keys->getFromServer('B9B60E76', 'keyserver.ubuntu.com');

        $pkgName = 'psi-plus';

        $repos = $this->getDoctrine()->getRepository('Ivan1986DebBundle:Repository');
        /** @var $repos RepositoryRepository */
        $repo = $repos->createFromAptString('deb http://ppa.launchpad.net/psi-plus/ppa/ubuntu oneiric main');

        $fs = new Filesystem();
        $fs->mirror($this->path.'/tmpl', $this->path.'/'.$pkgName);
        $dir = $this->path.'/'.$pkgName;
        $templater = $this->get('templating');
        /** @var $templater \Symfony\Bundle\TwigBundle\TwigEngine */
        $control = $templater->render('Ivan1986DebBundle:Builder:control.txt.twig', array(

        ));
        file_put_contents($dir.'/debian/control', $control);
        //$p = new Process('')


        return new Response($this->path);

    }

}
