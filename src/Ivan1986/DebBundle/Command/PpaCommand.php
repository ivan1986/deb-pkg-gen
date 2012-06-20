<?php

namespace Ivan1986\DebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Ivan1986\DebBundle\Entity\Package;
use Ivan1986\DebBundle\Util\Builder;
use Ivan1986\DebBundle\Entity\PpaRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ivan1986\DebBundle\Entity\RepositoryRepository;

class PpaCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('deb:ppa:distrs')
            ->setDescription('Get distrs from Launcpad.net')
            ->addOption('update', null, InputOption::VALUE_NONE, 'If set, rescan and update')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        /** @var $doctrine \Doctrine\Bundle\DoctrineBundle\Registry */

        $rrepo = $doctrine->getRepository('Ivan1986DebBundle:PpaRepository');
        /** @var $rrepo RepositoryRepository */
        $repos = $rrepo->getPpaForScan($input->getOption('update'));
        foreach($repos as $repo)
        {
            /** @var $repo PpaRepository */
            var_dump($repo->getName());
        }
        $doctrine->getManager()->flush();
    }

}
