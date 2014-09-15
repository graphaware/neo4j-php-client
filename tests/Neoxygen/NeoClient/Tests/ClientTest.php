<?php

namespace Neoxygen\NeoClient\Tests;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Neoxygen\NeoClient\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testContainerIsNotFrozenOnConstruct()
    {
        $client = new Client();
        $sc = $client->getServiceContainer();

        $this->assertFalse($sc->isFrozen());
    }

    public function testDefaultAttributes()
    {
        $client = new Client();
        $this->assertEmpty($client->getConfiguration());
    }

    public function testAddingANewConnection()
    {
        $client = new Client();
        $client->addConnection('default', 'http', 'localhost', 7474);

        $this->assertArrayHasKey('default', $client->getConfiguration()['connections']);
        $client->addConnection('second', 'https', 'localhost', 7575);
        $this->assertArrayHasKey('second', $client->getConfiguration()['connections']);
        $this->assertCount(2, $client->getConfiguration()['connections']);
    }

    public function testEventListenerIsAdded()
    {
        $client = new Client();
        $client->addEventListener('foo.event', function($event) {});

        $this->assertCount(1, $client->getListeners());
        $client->build();
    }

    public function testLoggersAreRegistered()
    {
        $client = new Client();
        $logger = new Logger('default');
        $handler = new NullHandler(Logger::DEBUG);
        $logger->pushHandler($handler);
        $client->setLogger('default', $logger);

        $this->assertCount(1, $client->getLoggers());
    }


    public function testConnectionsAreRegistered()
    {
        $client = new Client();
        $client->addConnection('default', 'http', 'localhost', 7474)
            ->addConnection('second', 'https', 'localhost', 7575)
            ->build();

        $cm = $client->getConnectionManager();

        $this->assertCount(2, $cm->getConnections());
        $this->assertEquals('default', $cm->getConnection('default')->getAlias());
        $this->assertEquals('default', $client->getConnection('default')->getAlias());
    }
}