<?php

namespace Ivan1986\DebBundle\Command;

use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Entity\SysPackage;
use Ivan1986\DebBundle\Model\GpgLoader;
use Ivan1986\DebBundle\Repository\PackageRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('deb:init')
            ->setDescription('Init repository package')
            ->addOption('clear', null, InputOption::VALUE_NONE, 'If set, clear old package')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $this->getContainer()->get('doctrine')->getRepository('Ivan1986DebBundle:GpgKey')
            ->getFromServer($this->getContainer()->getParameter('key'), $this->getContainer()->getParameter('key_server'));
        $repoBase = 'http://'.$this->getContainer()->getParameter('host').$this->getContainer()->get('router')->generate('repo', []);

        //Инициализируем конфигурацию чекера апта
        $kernel = $this->getContainer()->get('kernel');
        $path = $kernel->locateResource('@Ivan1986DebBundle/apt');
        file_put_contents($path.'/etc/trusted.gpg.d/repo-self.gpg', $key->getData());
        file_put_contents($path.'/etc/sources.list', 'deb '.$repoBase.' apttest main');

        //Инициализируем репозитории
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var $doctrine \Doctrine\Bundle\DoctrineBundle\Registry */
        $pkgs = $doctrine->getRepository('Ivan1986DebBundle:SysPackage');
        /** @var $pkgs PackageRepository */
        $pkg = $pkgs->getSystem();
        if ($pkg) {
            if ($input->getOption('clear')) {
                foreach ($pkg as $p) {
                    $doctrine->getManager()->remove($p);
                }
                $doctrine->getManager()->flush();
            } else {
                return;
            }
        }

        //репозитории
        $repo = new Repository();
        $repo->setRepoString($repoBase.' stable main');
        $repo->setSrc(false);
        $repo->setKey($key);
        $repo->setName('self');

        $pkg = $this->getContainer()->get('ivan1986_deb.builder')->build($repo);
        $package = new SysPackage();
        $package->setContent($pkg['content']);
        $package->setFile($pkg['file']);
        $package->setInfo($pkg['finfo']);

        $doctrine->getManager()->persist($package);
        $doctrine->getManager()->flush();

        //пакеты
        $repo = new Repository();
        $repo->setRepoString($repoBase.' link main');
        $repo->setSrc(false);
        $repo->setKey($key);
        $repo->setName('link');

        $pkg = $this->getContainer()->get('ivan1986_deb.builder')->build($repo);
        $package = new SysPackage();
        $package->setContent($pkg['content']);
        $package->setFile($pkg['file']);
        $package->setInfo($pkg['finfo']);

        $doctrine->getManager()->persist($package);
        $doctrine->getManager()->flush();
    }
}
