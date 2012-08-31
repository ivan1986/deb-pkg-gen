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
        $crawler = $this->client->click($crawler->selectLink('Add new repository')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'std[repoString]' =>
                'http://ppa.launchpad.net/ivan1986/ppa/ubuntu natty main',
            'std[bin]' => 1,
            'std[src]' => 1,
            'std[name]' => 'phpunit-test',
            'std[key][id]' => 'B9B60E76',
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

        $crawler = $this->client->click($crawler->selectLink('Delete')->link());
        $crawler = $this->client->followRedirect();

        // Check data in the show view
        $this->assertTrue($crawler->filter('td:contains("phpunit-test")')->count() == 0);
    }

    public function testPPARepo()
    {
        // Create a new entry in the database
        $crawler = $this->client->request('GET', '/repos/all');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->click($crawler->selectLink('Add new PPA repository')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'ppa[repoString]' => 'ppa:libreoffice/ppa',
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

        $crawler = $this->client->click($crawler->selectLink('Delete')->link());
        $crawler = $this->client->followRedirect();

        // Check data in the show view
        $this->assertTrue($crawler->filter('td:contains("libreoffice/ppa")')->count() == 0);
    }

    public function testNoPPARepo()
    {
        // Create a new entry in the database
        $crawler = $this->client->request('GET', '/repos/all');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->click($crawler->selectLink('Add new PPA repository')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'ppa[repoString]' => 'non/exist/repo',
        ));

        $this->client->submit($form);
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('h1:contains("New repository")')->count() > 0);
    }

    public function testStdRepoNoKey()
    {
        // Create a new entry in the database
        $crawler = $this->client->request('GET', '/repos/all');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->click($crawler->selectLink('Add new repository')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'std[repoString]' =>
            'http://ya.ru',
            'std[bin]' => 1,
            'std[src]' => 1,
            'std[name]' => 'non-exist-key-test',
            'std[key][id]' => '1024R/ffffff',
        ));

        $this->client->submit($form);
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $this->assertTrue($crawler->filter('h1:contains("New repository")')->count() > 0);
    }

}