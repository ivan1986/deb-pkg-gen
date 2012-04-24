<?php

namespace Ivan1986\DebBundle\Tests\Util;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;
use Ivan1986\DebBundle\Entity\RepositoryRepository;
use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Entity\Package;
use Ivan1986\DebBundle\Util\Builder;

class BuilderTest extends WebTestCase
{

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var \Symfony\Bundle\TwigBundle\TwigEngine */
    protected $tmpl;

    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->tmpl =  $kernel->getContainer()->get('templating');
    }

    public function testPackageCreate()
    {
        $keys = $this->em->getRepository('Ivan1986DebBundle:GpgKey');
        /** @var $keys GpgKeyRepository */
        $id = '28FA7071';
        $key = $keys->getFromServer($id, 'keyserver.ubuntu.com');
        $repo = $this->em->getRepository('Ivan1986DebBundle:Repository');
        /** @var $repo RepositoryRepository */
        $rep = $repo->createFromAptString('deb http://ppa.launchpad.net/psi-plus/ppa/ubuntu oneiric main');
        /** @var $rep Repository */
        $rep->setKey($key);
        $rep->setName('phpunit-test');

        $builder = new Builder($this->tmpl);
        $package = $builder->simplePackage($rep);
        $this->assertTrue($package instanceof Package);
        $now = new \DateTime();
        $this->assertEquals($package->getFile(), 'repo-phpunit-test_0.'.($now->format('Ymd').'_all.deb'));
        $this->assertTrue(strpos($package->getInfo(), 'http://ppa.launchpad.net/psi-plus/ppa/ubuntu')>0);
    }

}
