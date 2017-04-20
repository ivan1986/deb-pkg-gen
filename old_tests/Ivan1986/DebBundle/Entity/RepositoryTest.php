<?php

namespace Tests\Ivan1986\DebBundle\Entity;

use Ivan1986\DebBundle\Entity\Repository;

class RepositoryTest extends Entity
{
    public static function providerPPA()
    {
        return [
            ['deb http://ppa.launchpad.net/psi-plus/ppa/ubuntu oneiric main '],
            ['deb-src http://ppa.launchpad.net/ivan1986/ppa/ubuntu precise main '],
        ];
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
        return [
            ['http://repo.ru/ sid main contrib non-free'],
            ['deb http://repo.ru/ sid main contrib non-free'],
            ['deb-src http://repo.ru/ sid main contrib non-free'],

            //тесты на пробелы
            [' deb   http://repo.ru/   sid   main '],
            ['   deb http://repo.ru/   sid   main        '],
            ['http://repo.ru/   sid   main', 'sid'],
        ];
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
        return [
            [''],
            ['deb'],
            ['deb-src'],
            ['http://repo.ru/'],
            ['deb http://repo.ru/'],
            ['deb-src http://repo.ru/'],
            ['deb bla bla bla bla'],
        ];
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
        return [
            ['http://repo.ru/ ./'],
            ['deb http://repo.ru/ ./'],
            ['deb-src http://repo.ru/ ./'],
            ['file:///home/ ./'],
            ['deb file:///home/ ./'],
            ['deb-src file:///home/ ./'],
        ];
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
