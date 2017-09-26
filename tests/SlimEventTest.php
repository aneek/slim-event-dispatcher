<?php

namespace Slim\Event\Tests;

use Slim\Container;
use Slim\App;
use Slim\Http\Body;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;
use PHPUnit_Framework_TestCase;
use Slim\Event\SlimEventManager;

/**
 * Event Manager test class.
 *
 * @package Slim\Event\Tests
 */
class SlimEventTest extends PHPUnit_Framework_TestCase
{

    /**
     * An array of event listners.
     *
     * @var array
     */
    protected $events = [];

    /**
     * Event manager Instance.
     *
     * @var \Slim\Event\SlimEventManager
     */
    protected $eventManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->events = [
            'event.one' => [
                'listener' => \Slim\Event\Tests\TestListner::class,
            ],
        ];

        $this->eventManager = new SlimEventManager($this->events);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {

    }

    /**
     * @covers SlimEventManager::addListners()
     */
    public function testAddListners()
    {
        $this->assertTrue($this->eventManager->hasListeners('event.one'));
    }

    /**
     * @covers SlimEventManager::add()
     */
    public function testAdd()
    {
        $this->eventManager->add('event.two', function ($event) {
            return $event;
        });

        // Check if this listner is set or not.
        $this->assertTrue($this->eventManager->hasListeners('event.two'));
    }

    /**
     * Tests if the Class instance is properly initiated.
     *
     * @return void
     */
    public function testWithContainer()
    {
        $container = new Container();
        $container['event_manager'] = function () {
            $emitter = new SlimEventManager();
            return $emitter;
        };
        // Check if container is set properly or not.
        $this->assertTrue($container->has('event_manager'));

        // The container should be an instance of SlimEventManager.
        $this->assertInstanceOf('\Slim\Event\SlimEventManager', $container->get('event_manager'));
    }

    /**
     * @covers SlimEventManager::emit()
     */
    public function testEmitEvent()
    {
        // Setup container.
        $container = new Container();
        $events = $this->events;
        $container['event_manager'] = function ($c) use($events) {
            $emitter = new SlimEventManager($events);
            return $emitter;
        };

        // Prepare the Request and the application.
        $app = new App($container);
        // Setup a demo environment
        $env = Environment::mock([
          'SCRIPT_NAME' => '/index.php',
          'REQUEST_URI' => '/foo',
          'REQUEST_METHOD' => 'GET',
          'REMOTE_ADDR' => '127.0.0.1',
        ]);
        $uri = Uri::createFromEnvironment($env);
        $headers = Headers::createFromEnvironment($env);
        $headers->set('Accept', 'application/json');
        $cookies = [];
        $serverParams = $env->all();
        $body = new Body(fopen('php://temp', 'r+'));
        $req = new Request('GET', $uri, $headers, $cookies, $serverParams, $body);
        $res = new Response();
        $app->getContainer()['request'] = $req;
        $app->getContainer()['response'] = $res;

        $param = 'on foo page';

        $app->get('/foo', function ($req, $res) use ($param) {
            // Emit an event.
            $this->get('event_manager')->emit('event.one', $param);
            $emitted = $this->get('event_manager')->getListeners('event.one');
            $res->getBody()->write($emitted[0]->getResponse());
            return $res;
        });

        $resOut = $app->run();
        $body = (string) $resOut->getBody();
        $this->assertEquals($param, $body);
    }
}

