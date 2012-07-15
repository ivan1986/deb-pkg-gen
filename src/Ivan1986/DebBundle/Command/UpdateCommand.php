<?php

namespace Ivan1986\DebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\Filesystem;
use Zend\Cache\Storage\Adapter\FilesystemOptions;

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
        if (!count($repos))
            return;
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

        $dir = $this->getContainer()->getParameter('cache_dir');
        if (!is_dir($dir))
            mkdir($dir, 0777, true);
        $opt = new FilesystemOptions();
        $opt->setCacheDir($dir);
        $opt->setDirPerm(0777);
        $opt->setFilePerm(0666);
        $cache = StorageFactory::factory(array(
            'adapter' => 'filesystem',
        ));
        /** @var $cache Filesystem */
        $cache->setOptions($opt);
        $cache->clearByPrefix('repo_');

    }

}
