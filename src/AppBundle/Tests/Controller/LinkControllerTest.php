<?php

namespace AppBundle\Tests\Controller;

class LinkControllerTest extends AbstractTestController
{

    public function testCompleteScenario()
    {
        // Create a new entry in the database
        $crawler = $this->client->request('GET', '/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /");

        $this->assertGreaterThan(0, $crawler->filter('.row.link.box')->count(), 'No links');
/*
$crawler = $client->click($crawler->selectLink('Create a new entry')->link());

// Fill in the form and submit it
$form = $crawler->selectButton('Create')->form(array(
'appbundle_link[field_name]' => 'Test',
// ... other fields to fill
));

$client->submit($form);
$crawler = $client->followRedirect();

// Check data in the show view
$this->assertGreaterThan(0, $crawler->filter('td:contains("Test")')->count(), 'Missing element td:contains("Test")');

// Edit the entity
$crawler = $client->click($crawler->selectLink('Edit')->link());

$form = $crawler->selectButton('Update')->form(array(
'appbundle_link[field_name]' => 'Foo',
// ... other fields to fill
));

$client->submit($form);
$crawler = $client->followRedirect();

// Check the element contains an attribute with value equals "Foo"
$this->assertGreaterThan(0, $crawler->filter('[value="Foo"]')->count(), 'Missing element [value="Foo"]');

// Delete the entity
$client->submit($crawler->selectButton('Delete')->form());
$crawler = $client->followRedirect();

// Check the entity has been delete on the list
$this->assertNotRegExp('/Foo/', $client->getResponse()->getContent());*/
    }

}
