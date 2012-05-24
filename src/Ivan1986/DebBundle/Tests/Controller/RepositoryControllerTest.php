<?php

namespace Ivan1986\DebBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RepositoryControllerTest extends WebTestCase
{
    public $client;

    public function setUp()
    {
        $this->client = static::createClient();
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('_submit')->form(array(
            '_username'  => 'test',
            '_password'  => 'test',
        ));

        $this->client->submit($form);
    }

    public function testCompleteScenario()
    {
        // Create a new entry in the database
        $crawler = $this->client->request('GET', '/profile/repos/');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
        $crawler = $this->client->click($crawler->selectLink('Create a new entry')->link());

        return;
        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'ivan1986_debbundle_repositorytype[field_name]'  => 'Test',
            // ... other fields to fill
        ));

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        // Check data in the show view
        $this->assertTrue($crawler->filter('td:contains("Test")')->count() > 0);

        // Edit the entity
        $crawler = $this->client->click($crawler->selectLink('Edit')->link());

        $form = $crawler->selectButton('Edit')->form(array(
            'repository[field_name]'  => 'Foo',
            // ... other fields to fill
        ));

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        // Check the element contains an attribute with value equals "Foo"
        $this->assertTrue($crawler->filter('[value="Foo"]')->count() > 0);

        // Delete the entity
        $this->client->submit($crawler->selectButton('Delete')->form());
        $crawler = $this->client->followRedirect();

        // Check the entity has been delete on the list
        $this->assertNotRegExp('/Foo/', $this->client->getResponse()->getContent());
    }

}