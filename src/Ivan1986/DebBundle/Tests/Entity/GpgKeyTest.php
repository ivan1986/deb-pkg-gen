<?php

namespace Ivan1986\DebBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;
use Ivan1986\DebBundle\Entity\GpgKey;

class GpgKeyTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testGetMyKey()
    {
        $keys = $this->em->getRepository('Ivan1986DebBundle:GpgKey');
        /** @var $keys GpgKeyRepository */
        $id = '28FA7071';
        $key = $keys->getFromServer('28FA7071', 'keyserver.ubuntu.com');
        /** @var $key GpgKey */
        $this->assertEquals($key->getId(), $id);
        $this->assertEquals($key->getFingerprint(), '33C640DA31127882C496917F6831CF9528FA7071');
        //$this->assertEquals($key->getData(), '33C640DA31127882C496917F6831CF9528FA7071');
    }

}
