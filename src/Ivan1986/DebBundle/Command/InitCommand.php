<?php

namespace Ivan1986\DebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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

class InitCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('deb:init')
            ->setDescription('Init repository package')
            //->addArgument('name', InputArgument::OPTIONAL, 'Who do you want to greet?')
            ->addOption('clear', null, InputOption::VALUE_NONE, 'If set, clear old package')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = GpgLoader::getFromServer($this->getContainer()->getParameter('key'), $this->getContainer()->getParameter('key_server'));
        $repoBase = 'http://'.$this->getContainer()->getParameter('host').$this->getContainer()->get('router')->generate('repo', array());

        //Инициализируем конфигурацию чекера апта
        $t = $this->getContainer()->get('templating');
        $kernel = $this->getContainer()->get('kernel');
        $path = $kernel->locateResource('@Ivan1986DebBundle');
        /** @var $t TwigEngine Шаблонизатор */
        file_put_contents($path.'apt/apt.conf', $t->render('Ivan1986DebBundle:Repo:apt.conf.twig', array(
            'dir' => $path.'apt',
        )));
        file_put_contents($path.'apt/etc/trusted.gpg.d/repo-self.gpg', $key->getData());
        file_put_contents($path.'apt/etc/sources.list', 'deb '.$repoBase.' apttest main');

        //Инициализируем репозитории
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var $doctrine \Doctrine\Bundle\DoctrineBundle\Registry */

        $pkgs = $doctrine->getRepository('Ivan1986DebBundle:SysPackage');
        /** @var $pkgs PackageRepository */
        $pkg = $pkgs->getSystem();
        if ($pkg)
        {
            if ($input->getOption('clear'))
            {
                foreach($pkg as $p)
                    $doctrine->getManager()->remove($p);
                $doctrine->getManager()->flush();
            }
            else
                return;
        }

        //репозитории
        $repo = new Repository();
        $repo->setRepoString($repoBase.' stable main');
        $repo->setSrc(false);
        $repo->setKey($key);
        $repo->setName('self');

        $builder = new Builder($t);
        $pkg = $builder->build($repo);
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

        $builder = new Builder($t);
        $pkg = $builder->build($repo);
        $package = new SysPackage();
        $package->setContent($pkg['content']);
        $package->setFile($pkg['file']);
        $package->setInfo($pkg['finfo']);

        $doctrine->getManager()->persist($package);
        $doctrine->getManager()->flush();

    }

}
