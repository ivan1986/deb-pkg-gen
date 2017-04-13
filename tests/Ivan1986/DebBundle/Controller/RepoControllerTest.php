<?php

namespace Tests\Ivan1986\DebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RepoControllerTest extends WebTestCase
{
    public $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testPackages()
    {
        $crawler = $this->client->request('GET', '/repo/dists/stable/main/binary-i386/Packages');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
    }

    public function testRelease()
    {
        $crawler = $this->client->request('GET', '/repo/dists/stable/Release');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
    }

    public function testReleaseGpg()
    {
        $crawler = $this->client->request('GET', '/repo/dists/stable/Release.gpg');
        $this->assertTrue(200 === $this->client->getResponse()->getStatusCode());
    }
}
