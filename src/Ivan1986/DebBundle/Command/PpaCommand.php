<?php

namespace Ivan1986\DebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Anchovy\CURLBundle\CURL\Curl;
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
        $repos = $rrepo->getPpaForScan(!$input->getOption('update'));
        $curl = new Curl();
        foreach($repos as $repo)
        {
            /** @var $repo PpaRepository */
            $curl->setURL($repo->getPpaUrl().'dists/');
            $page = $curl->execute();
            $info = $curl->getInfo();
            if ($info['http_code']!=200)
                continue;
            $matches = array();
            preg_match_all('#<a href="([a-z]+)/">\1/</a>#i', $page, $matches);
            $dists = $matches[1];
            $distros = $repo->getDistrs();
            /** @var $distros \Ivan1986\DebBundle\Model\DistList */
            $repo->setDistrs($distros->update($dists, $this->getContainer()->getParameter('dists')));
        }
        $doctrine->getManager()->flush();
    }

}
