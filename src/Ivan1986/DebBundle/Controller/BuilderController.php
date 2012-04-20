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
use Ivan1986\DebBundle\Entity\Repository;

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
        /** @var $repo Repository */
        $repo->setKey($key);
        $repo->setName($pkgName);


        $fs = new Filesystem();
        $fs->mirror($this->path.'/tmpl', $this->path.'/'.$repo->pkgName());
        $dir = $this->path.'/'.$repo->pkgName();
        $templater = $this->get('templating');
        /** @var $templater \Symfony\Bundle\TwigBundle\TwigEngine */
        $files = array('control', 'changelog', 'install');
        foreach($files as $file)
        {
            file_put_contents($dir.'/debian/'.$file, $templater->render('Ivan1986DebBundle:Builder:'.$file.'.txt.twig', array(
                'repo' => $repo,
            )));
        }
        file_put_contents($dir.'/'.$repo->pkgName().'.list', $templater->render('Ivan1986DebBundle:Builder:list.txt.twig', array(
            'repo' => $repo,
        )));
        file_put_contents($dir.'/'.$repo->pkgName().'.gpg', $repo->getKey()->getData());

        //Сборка пакета
        $p = new Process('dpkg-buildpackage -b -uc -tc');
        $p->setEnv(array(
            'PATH' => '/bin:/usr/bin',
            'HOME' => $dir,
        ));
        $p->setWorkingDirectory($dir);
        $p->run();
        $p->getExitCode();


        return new Response($this->path);

    }

}
