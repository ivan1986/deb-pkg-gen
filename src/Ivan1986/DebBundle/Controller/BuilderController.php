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
        $key = $keys->getFromServer('28FA7071', 'keyserver.ubuntu.com');

        $pkgName = 'test';

        $fs = new Filesystem();
        $fs->mirror($this->path.'/tmpl', $this->path.'/'.$pkgName);
        $dir = $this->path.'/'.$pkgName;
        $this->render('Ivan1986DebBundle:Builder:control.twig');
        //$p = new Process('')


        return new Response($this->path);

    }

}
