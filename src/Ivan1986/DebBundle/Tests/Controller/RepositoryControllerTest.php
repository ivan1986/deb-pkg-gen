<?php

namespace Ivan1986\DebBundle\Tests\Controller;

use Ivan1986\DebBundle\Tests\Entity\Entity;
use Ivan1986\DebBundle\Entity\Repository;

class RepositoryControllerTest extends Entity
{
    public $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('_submit')->form(array(
            '_username'  => 'test',
            '_password'  => 'test',
        ));

        $this->client->submit($form);
    }

    public function testStdRepo()
    {
        // Create a new entry in the database
        $crawler = $this->client->request('GET', '/repos/all');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->click($crawler->selectLink('Добавить новый репозиторий')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Создать')->form(array(
            'ivan1986_debbundle_repositorytype[repoString]' =>
                'http://ppa.launchpad.net/ivan1986/ppa/ubuntu natty main',
            'ivan1986_debbundle_repositorytype[bin]' => 1,
            'ivan1986_debbundle_repositorytype[src]' => 1,
            'ivan1986_debbundle_repositorytype[name]' => 'phpunit-test',
            'ivan1986_debbundle_repositorytype[key][id]' => 'B9B60E76',
        ));

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        // Check data in the show view
        $this->assertTrue($crawler->filter('td:contains("phpunit-test")')->count() > 0);

        $item = $this->em->getRepository("Ivan1986DebBundle:Repository")
            ->findOneBy(array('name' => 'phpunit-test'));
        /** @var $item Repository */
        $id = $item->getId();
        $crawler = $this->client->request('GET', '/repos/'.$id.'/edit');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->click($crawler->selectLink('Удалить')->link());
        $crawler = $this->client->followRedirect();

        // Check data in the show view
        $this->assertTrue($crawler->filter('td:contains("phpunit-test")')->count() == 0);
    }

    public function testPPARepo()
    {
        // Create a new entry in the database
        $crawler = $this->client->request('GET', '/repos/all');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->click($crawler->selectLink('Добавить новый PPA репозиторий')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Создать')->form(array(
            'ivan1986_debbundle_pparepositorytype[repoString]' => 'ppa:libreoffice/ppa',
        ));

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        // Check data in the show view
        $this->assertTrue($crawler->filter('td:contains("libreoffice/ppa")')->count() > 0);

        $item = $this->em->getRepository("Ivan1986DebBundle:Repository")
            ->findOneBy(array('name' => 'ppa-libreoffice-ppa'));
        /** @var $item Repository */
        $id = $item->getId();
        $crawler = $this->client->request('GET', '/repos/'.$id.'/edit');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->click($crawler->selectLink('Удалить')->link());
        $crawler = $this->client->followRedirect();

        // Check data in the show view
        $this->assertTrue($crawler->filter('td:contains("libreoffice/ppa")')->count() == 0);
    }

    public function testNoPPARepo()
    {
        // Create a new entry in the database
        $crawler = $this->client->request('GET', '/repos/all');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->click($crawler->selectLink('Добавить новый PPA репозиторий')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Создать')->form(array(
            'ivan1986_debbundle_pparepositorytype[repoString]' => 'non/exist/repo',
        ));

        $this->client->submit($form);
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('h1:contains("Новый репозиторий")')->count() > 0);
    }

    public function testStdRepoNoKey()
    {
        // Create a new entry in the database
        $crawler = $this->client->request('GET', '/repos/all');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->click($crawler->selectLink('Добавить новый репозиторий')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Создать')->form(array(
            'ivan1986_debbundle_repositorytype[repoString]' =>
            'http://ya.ru',
            'ivan1986_debbundle_repositorytype[bin]' => 1,
            'ivan1986_debbundle_repositorytype[src]' => 1,
            'ivan1986_debbundle_repositorytype[name]' => 'non-exist-key-test',
            'ivan1986_debbundle_repositorytype[key][id]' => '1024R/ffffff',
        ));

        $this->client->submit($form);
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('h1:contains("Новый репозиторий")')->count() > 0);
    }

}