<?php

namespace Ivan1986\DebBundle\Tests\Entity;

use Ivan1986\DebBundle\Entity\RepositoryRepository;
use Ivan1986\DebBundle\Entity\Repository;
use Ivan1986\DebBundle\Exception\ParseRepoStringException;

class RepositoryTest extends Entity
{

    public static function providerPPA()
    {
        return array(
            array('deb http://ppa.launchpad.net/psi-plus/ppa/ubuntu oneiric main ',
                'http://ppa.launchpad.net/psi-plus/ppa/ubuntu', 'oneiric'),
            array('deb-src http://ppa.launchpad.net/ivan1986/ppa/ubuntu precise main ',
                'http://ppa.launchpad.net/ivan1986/ppa/ubuntu', 'precise'),
        );
    }

    /**
     * @dataProvider providerPPA
     */
    public function testPPA($string, $url, $rel)
    {
        $r = new Repository();
        $r->setRepoString($string);
        $this->assertTrue($r instanceof Repository);
        $this->assertEquals($r->getUrl(), $url);
        $this->assertEquals($r->getRelease(), $rel);
        $this->assertEquals($r->getComponents(), array('main'));
    }

    public static function providerRepo()
    {
        return array(
            array('http://repo.ru/ sid main contrib non-free', 'sid', array('main', 'contrib', 'non-free')),
            array('deb http://repo.ru/ sid main contrib non-free', 'sid', array('main', 'contrib', 'non-free')),
            array('deb-src http://repo.ru/ sid main contrib non-free', 'sid', array('main', 'contrib', 'non-free')),

            //тесты на пробелы
            array(' deb   http://repo.ru/   sid   main ', 'sid', array('main')),
            array('   deb http://repo.ru/   sid   main        ', 'sid', array('main')),
            array('http://repo.ru/   sid   main', 'sid', array('main')),
        );
    }

    /**
     * @dataProvider providerRepo
     */
    public function testStdRepo($string, $rel, $components)
    {
        $r = new Repository();
        $r->setRepoString($string);
        $this->assertEquals($r->getUrl(), 'http://repo.ru/');
        $this->assertEquals($r->getRelease(), $rel);
        $this->assertEquals($r->getComponents(), $components);
    }

    //<editor-fold defaultstate="collapsed" desc="Ошибки">
    public static function providerError()
    {
        return array(
            array(''),
            array('deb'),
            array('deb-src'),
            array('http://repo.ru/'),
            array('deb http://repo.ru/'),
            array('deb-src http://repo.ru/'),
        );
    }

    /**
     * @expectedException Ivan1986\DebBundle\Exception\ParseRepoStringException
     * @dataProvider providerError
     */
    public function testParseError($string)
    {
        $r = new Repository();
        $r->setRepoString($string);
    }
    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="Простой старый репозиторий">
    public static function providerSimple()
    {
        return array(
            array('http://repo.ru/ ./'),
            array('deb http://repo.ru/ ./'),
            array('deb-src http://repo.ru/ ./'),
        );
    }

    /**
     * @dataProvider providerSimple
     */
    public function testParseSimple($string)
    {
        $r = new Repository();
        $r->setRepoString($string);
        $this->assertEquals($r->getRelease(), './');
        $this->assertEquals($r->getUrl(), 'http://repo.ru/');
    }
    //</editor-fold>

}
