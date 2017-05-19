<?php

namespace Ivan1986\DebBundle\Command;

use Ivan1986\DebBundle\Entity\PpaRepository;
use Ivan1986\DebBundle\Model\DistList;
use Ivan1986\DebBundle\Repository\RepositoryRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PpaCommand extends ContainerAwareCommand
{
    /** @var \GuzzleHttp\Client() */
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
        $this->curl = new \GuzzleHttp\Client(['http_errors' => false]);
        $distList = new DistList();
        foreach ($repos as $repo) {
            /** @var $repo PpaRepository */
            $res = $this->curl->get($repo->getPpaUrl().'dists/');
            if ($res->getStatusCode() != 200) {
                continue;
            }
            $matches = [];
            preg_match_all('#<a href="([a-z]+)/">\1/</a>#i', $res->getBody(), $matches);
            $dists = $this->checkNotEmpty($repo->getPpaUrl().'dists/', $matches[1]);
            if (empty($dists)) {
                continue;
            }
            $repo->setDistrs($distList->update($dists));
            $doctrine->getManager()->flush($repo);
        }
    }

    /**
     * Filter distributions without packages.
     *
     * @param string $base
     * @param array  $distributions
     *
     * @return array
     */
    protected function checkNotEmpty($base, $distributions)
    {
        foreach ($distributions as $k => $dist) {
            $res = $this->curl->get($base.$dist.'/main/binary-amd64/Packages.gz');
            if ($res->getHeaderLine('Content-Length') < 50) {
                unset($distributions[$k]);
            }
        }

        return array_values($distributions);
    }
}
