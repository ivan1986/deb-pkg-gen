<?php

namespace Tests\Ivan1986\DebBundle\Entity;

use Ivan1986\DebBundle\Entity\RepositoryRepository;
use Ivan1986\DebBundle\Entity\Repository;

class RepositoryTest extends Entity
{

    public static function providerPPA()
    {
        return array(
            array('deb http://ppa.launchpad.net/psi-plus/ppa/ubuntu oneiric main '),
            array('deb-src http://ppa.launchpad.net/ivan1986/ppa/ubuntu precise main '),
        );
    }

    /**
     * @dataProvider providerPPA
     */
    public function testPPA($string)
    {
        $r = new Repository();
        $r->setRepoString($string);
        $this->assertTrue($r instanceof Repository);
        $this->assertTrue($r->isValidRepoString());
    }

    public static function providerRepo()
    {
        return array(
            array('http://repo.ru/ sid main contrib non-free'),
            array('deb http://repo.ru/ sid main contrib non-free'),
            array('deb-src http://repo.ru/ sid main contrib non-free'),

            //тесты на пробелы
            array(' deb   http://repo.ru/   sid   main '),
            array('   deb http://repo.ru/   sid   main        '),
            array('http://repo.ru/   sid   main', 'sid'),
        );
    }

    /**
     * @dataProvider providerRepo
     */
    public function testStdRepo($string)
    {
        $r = new Repository();
        $r->setRepoString($string);
        $this->assertTrue($r->isValidRepoString());
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
            array('deb bla bla bla bla'),
        );
    }

    /**
     * @dataProvider providerError
     */
    public function testParseError($string)
    {
        $r = new Repository();
        $r->setRepoString($string);
        $this->assertFalse($r->isValidRepoString());
    }
    //</editor-fold>

    //<editor-fold defaultstate="collapsed" desc="Простой старый репозиторий">
    public static function providerSimple()
    {
        return array(
            array('http://repo.ru/ ./'),
            array('deb http://repo.ru/ ./'),
            array('deb-src http://repo.ru/ ./'),
            array('file:///home/ ./'),
            array('deb file:///home/ ./'),
            array('deb-src file:///home/ ./'),
        );
    }

    /**
     * @dataProvider providerSimple
     */
    public function testParseSimple($string)
    {
        $r = new Repository();
        $r->setRepoString($string);
        $this->assertTrue($r->isValidRepoString());
    }
    //</editor-fold>

}
