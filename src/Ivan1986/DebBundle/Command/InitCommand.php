<?php

namespace Ivan1986\DebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ivan1986\DebBundle\Entity\PackageRepository;

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
        //$keys = $this->getDoctrine()->getRepository('Ivan1986DebBundle:GpgKey');
        /** @var $keys GpgKeyRepository */
        //$key = $keys->getFromServer($this->container->getParameter('key'), 'keyserver.ubuntu.com');
        $pkgs = $this->getContainer()->get('doctrine')->getRepository('Ivan1986DebBundle:Package');
        /** @var $pkgs PackageRepository */
        $pkgs->getSystem();
    }

}
