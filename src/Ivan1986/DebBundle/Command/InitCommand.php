<?php

namespace Ivan1986\DebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Ivan1986\DebBundle\Model\GpgLoader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var $doctrine \Doctrine\Bundle\DoctrineBundle\Registry */

        $pkgs = $doctrine->getRepository('Ivan1986DebBundle:SysPackage');
        /** @var $pkgs PackageRepository */
        $pkg = $pkgs->getSystem();
        if ($pkg)
        {
            if ($input->getOption('clear'))
            {
                $doctrine->getManager()->remove($pkg);
                $doctrine->getManager()->flush();
            }
            else
                return;
        }

        $key = GpgLoader::getFromServer($this->getContainer()->getParameter('key'), $this->getContainer()->getParameter('key_server'));

        $repo = new Repository();
        $repo->setRepoString('http://'.$this->getContainer()->getParameter('host').$this->getContainer()->get('router')->generate('repo', array()).' stable main');
        $repo->setSrc(false);
        $repo->setKey($key);
        $repo->setName($this->getContainer()->getParameter('host'));

        $builder = new Builder($this->getContainer()->get('templating'));
        $pkg = $builder->build($repo);
        $package = new SysPackage();
        $package->setUser(null);
        $package->setContent($pkg['content']);
        $package->setFile($pkg['file']);
        $package->setInfo($pkg['finfo']);

        $doctrine->getManager()->persist($package);
        $doctrine->getManager()->flush();
    }

}
