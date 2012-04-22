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

        $this->buildPackage($repo);

        return new Response($this->path);

    }

    public function buildPackage(Repository $repo)
    {
        $dir = $this->path.'/'.$repo->pkgName();

        $lockf = $dir.'.lock';
        $lockr = fopen($lockf, 'w');
        if (!$lockr)
            return false;
        $lock = flock($lockr, LOCK_EX | LOCK_NB);
        if (!$lock)
            return false;
        //Копирование и изменение шаблона
        $fs = new Filesystem();
        $fs->mirror($this->path.'/tmpl', $dir);
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

        $env = array(
            'PATH' => '/bin:/usr/bin',
            'HOME' => $dir,
        );
        //Сборка пакета
        $p = new Process('dpkg-buildpackage -b -uc -tc');
        $p->setEnv($env);
        $p->setWorkingDirectory($dir);
        $p->run();
        $exit = $p->getExitCode();
        if ($exit)
            return false;

        //парсинг файла changes
        $out = $p->getErrorOutput();
        $pattern = 'dpkg-genchanges -b >../';
        $fname = substr($out, strpos($out, $pattern) + strlen($pattern));
        $fname = trim(substr($fname, 0, strpos($fname, 'dpkg-genchanges')));
        $finfo = file($this->path.'/'.$fname);
        $parse = array();
        foreach($finfo as $k=>$line)
        {
            $line = trim($line);
            if (!isset($finfo[$k+1]))
                break;
            $line2 = trim($finfo[$k+1]);
            if ($line == 'Checksums-Sha1:')
                $parse['str-sha1'] = $line2;
            if ($line == 'Checksums-Sha256:')
                $parse['str-sha256'] = $line2;
            if ($line == 'Files:')
                $parse['str-file'] = $line2;
        }
        $info = array();
        $str = explode(' ',$parse['str-sha1']);
        $info['SHA1'] = $str[0];
        $str = explode(' ',$parse['str-sha256']);
        $info['SHA256'] = $str[0];
        $str = explode(' ',$parse['str-file']);
        $info['MD5sum'] = $str[0];
        $info['Size'] = $str[1];
        $info['Filename'] = $file = $str[4];
        $content = file_get_contents($this->path.'/'.$file);

        $p = new Process('dpkg --info '.$this->path.'/'.$file);
        $p->setEnv($env);
        $p->setWorkingDirectory($dir);
        $p->run();
        $exit = $p->getExitCode();
        if ($exit)
            return false;
        $finfo = $p->getOutput();
        $finfo = str_replace("\n ", "\n", $finfo);
        $finfo = substr($finfo, strpos($finfo, 'Package:'));
        $fileinfo = '';
        $fileinfo .= "Filename: %filename%\n";
        $fileinfo .= 'Size: '.$info['Size']."\n";
        $fileinfo .= 'SHA256: '.$info['SHA256']."\n";
        $fileinfo .= 'SHA1: '.$info['SHA1']."\n";
        $fileinfo .= 'MD5sum: '.$info['MD5sum']."\n";
        $finfo = str_replace("Description:", $fileinfo."Description:", $finfo);

        /**
         * Теперь у нас есть:
         *
         * $content - содержимое пакета
         * $file - имя файла
         *
         * $finfo - блок для списка пакетов
         * в блоке прописаны суммы и есть тег %fulename%
         * для подстановки полного имени файла
         */


        unlink($this->path.'/'.$file);
        unlink($this->path.'/'.$fname);

        $fs->remove($dir);
        flock($lockr, LOCK_UN);
        fclose($lockr);
        unlink($lockf);
    }

}
