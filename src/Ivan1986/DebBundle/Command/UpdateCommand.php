<?php

namespace Ivan1986\DebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Ivan1986\DebBundle\Entity\PpaRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ivan1986\DebBundle\Entity\RepositoryRepository;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;
use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Entity\SysPackage;
use Ivan1986\DebBundle\Entity\Package;
use Ivan1986\DebBundle\Util\Builder;

class UpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('deb:update')
            ->setDescription('Update and create packages from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var $doctrine \Doctrine\Bundle\DoctrineBundle\Registry */

        $rrepo = $doctrine->getRepository('Ivan1986DebBundle:Repository');
        /** @var $rrepo RepositoryRepository */
        $repos = $rrepo->getNewAndUpdated();
        $builder = new Builder($this->getContainer()->get('templating'));
        foreach($repos as $repo)
        {
            /** @var $repo Repository */
            foreach($repo->getPackages() as $pkg)
            {
                /** @var $pkg Package */
                $doctrine->getManager()->remove($pkg);
            }
            $repo->buildPackages($builder, $doctrine->getManager());
        }
        $doctrine->getManager()->flush();
    }

}
