<?php

namespace Tests\Ivan1986\DebBundle\Util;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Ivan1986\DebBundle\Model\GpgLoader;

use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;
use Ivan1986\DebBundle\Entity\RepositoryRepository;
use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Entity\SimplePackage;
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
        $key = GpgLoader::getFromServer($id, 'keyserver.ubuntu.com');
        $rep = new Repository();
        $rep->setRepoString('deb http://ppa.launchpad.net/psi-plus/ppa/ubuntu oneiric main');
        $rep->setKey($key);
        $rep->setName('phpunit-test');

        $builder = new Builder($this->tmpl);
        $package = $builder->simplePackage($rep);
        $this->assertTrue($package instanceof SimplePackage);
        $now = new \DateTime();
        $this->assertEquals($package->getFile(), 'repo-phpunit-test_0.'.($now->format('Ymd').'_all.deb'));
        $this->assertTrue(strpos($package->getInfo(), 'http://ppa.launchpad.net/psi-plus/ppa/ubuntu')>0);
    }

}
