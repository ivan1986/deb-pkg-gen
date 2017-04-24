<?php

namespace Ivan1986\DebBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanUsersCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('clean:users')
            ->setDescription('Cleanup non active users without packages')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');

        $inactive = $doctrine->getRepository('Ivan1986DebBundle:User')->getInactive();
        foreach($inactive->getResult() as $user)
        {
            /** @var $user \Ivan1986\DebBundle\Entity\User */
            $doctrine->getManager()->remove($user);
        }

        $doctrine->getManager()->flush();
        $output->writeln('Command result.');
    }

}
