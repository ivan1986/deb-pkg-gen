<?php

namespace Tests\Ivan1986\DebBundle\Model;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Ivan1986\DebBundle\Entity\GpgKeyRepository;
use Ivan1986\DebBundle\Model\GpgLoader;
use Ivan1986\DebBundle\Entity\GpgKey;
use Ivan1986\DebBundle\Exception\GpgNotFoundException;

class GpgKeyTest extends WebTestCase
{
    public function testGetMyKey()
    {
        $id = '28FA7071';
        $key = GpgLoader::getFromServer($id, 'keyserver.ubuntu.com');
        /** @var $key GpgKey */
        $this->assertEquals($key->getId(), $id);
        $this->assertEquals($key->getFingerprint(), '33C640DA31127882C496917F6831CF9528FA7071');
        $this->assertTrue(strpos($key->getData(), 'Ivan Borzenkov <ivan1986@list.ru>')>0);
    }

    /**
     * @expectedException Ivan1986\DebBundle\Exception\GpgNotFoundException
     */
    public function testGetNotExistKey()
    {
        $id = 'ffffff';
        $key = GpgLoader::getFromServer($id, 'keyserver.ubuntu.com');
    }

}
