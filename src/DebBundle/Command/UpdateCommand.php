<?php

namespace Ivan1986\DebBundle\Command;

use Ivan1986\DebBundle\Entity\Package;
use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Repository\RepositoryRepository;
use Ivan1986\DebBundle\Service\Builder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $builder = $this->getContainer()->get('ivan1986_deb.builder');
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var $doctrine \Doctrine\Bundle\DoctrineBundle\Registry */
        $rrepo = $doctrine->getRepository('Ivan1986DebBundle:Repository');
        /** @var $rrepo RepositoryRepository */
        $repos = $rrepo->getNewAndUpdated();
        if (!count($repos)) {
            return;
        }

        foreach ($repos as $repo) {
            /** @var $repo Repository */
            foreach ($repo->getPackages() as $pkg) {
                /* @var $pkg Package */
                $doctrine->getManager()->remove($pkg);
            }
            $repo->buildPackages($builder, $doctrine->getManager());
        }
        $doctrine->getManager()->flush();

        $this->getContainer()->get('doctrine_cache.providers.repo_cache')->flushAll();
    }
}
