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
    /** @var Curl */
    protected $curl;

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
        $this->curl = new Curl();
        foreach($repos as $repo)
        {
            /** @var $repo PpaRepository */
            $this->curl->setURL($repo->getPpaUrl().'dists/');
            $this->curl->setOption('CURLOPT_HEADER', false);
            $page = $this->curl->execute();
            $info = $this->curl->getInfo();
            if ($info['http_code']!=200)
                continue;
            $matches = array();
            preg_match_all('#<a href="([a-z]+)/">\1/</a>#i', $page, $matches);
            $dists = $this->checkNotEmpty($repo->getPpaUrl().'dists/', $matches[1]);
            $distros = $repo->getDistrs();
            /** @var $distros \Ivan1986\DebBundle\Model\DistList */
            $repo->setDistrs($distros->update($dists, $this->getContainer()->getParameter('dists')));
        }
        $doctrine->getManager()->flush();
    }

    protected function checkNotEmpty($base, $dists)
    {
        $this->curl->setOption('CURLOPT_HEADER', true);
        foreach($dists as $k=>$dist)
        {
            $this->curl->setURL($base.$dist.'/main/binary-i386/Packages');
            $info = $this->curl->getInfo();
            if ($info['size_download'] == 0)
                unset($dists[$k]);
        }
        return array_values($dists);
    }

}
