<?php

namespace Ivan1986\DebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Filesystem;
use Zend\Cache\Storage\Adapter\FilesystemOptions;
use Ivan1986\DebBundle\Entity\LinkPackage;
use Symfony\Component\Process\Process;
use Ivan1986\DebBundle\Model\GpgLoader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;

use Ivan1986\DebBundle\Entity\PackageRepository;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;
use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Entity\SysPackage;
use Ivan1986\DebBundle\Util\Builder;

class CheckCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('deb:check')
            ->setDescription('Check one package')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        //Инициализируем репозитории
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var $doctrine \Doctrine\Bundle\DoctrineBundle\Registry */

        $repo = $doctrine->getRepository('Ivan1986DebBundle:LinkPackage');
        /** @var $repo PackageRepository */
        if (!$repo->markOneForTest())
            return;

        $dir = dirname(__DIR__).'/apt';
        $env = array(
            'PATH' => '/bin:/usr/bin',
            'HOME' => $dir,
        );
        //Проверка аптом корректности работы
        $p = new Process('apt-get -c apt.conf -qq update');
        $p->setEnv($env);
        $p->setWorkingDirectory($dir);
        $p->run();
        $ok = $p->getErrorOutput() === "";
        $repo->setResultForTest($ok ? LinkPackage::CHECK_YES : LinkPackage::CHECK_ERR);

        //чистим за аптом файлы
        $files = array(
            $dir.'/cache/pkgcache.bin',
            $dir.'/cache/srcpkgcache.bin',
            $dir.'/lock',
        );
        $files = array_merge($files, glob($dir.'/state/lists/*'));
        foreach($files as $file)
            if (is_file($file))
                unlink($file);

        $dir = $this->getContainer()->getParameter('cache_dir');
        if (!is_dir($dir))
            mkdir($dir, 0777, true);
        $opt = new FilesystemOptions();
        $opt->setCacheDir($dir);
        $opt->setDirPermission(0777);
        $opt->setFilePermission(0666);
        $cache = StorageFactory::factory(array(
                'adapter' => 'filesystem',
            ));
        /** @var $cache Filesystem */
        $cache->setOptions($opt);
        $cache->clearByPrefix('repo_');

    }

}
