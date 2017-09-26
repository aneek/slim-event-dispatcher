<?php

namespace Slim\Event;

use League\Event\EmitterTrait;
use League\Event\ListenerAcceptorInterface;
use League\Event\CallbackListener;

/**
 * Slim Event Manager class.
 *
 * @package Slim\Event
 */
class SlimEventManager
{
    use EmitterTrait;

    /**
     * Array of Subscribers.
     *
     * @var array
     */
    protected $subscribers = [];

    /**
     * Class Constructor.
     *
     * @param array $events
     *   An array containing the application's event(s).
     */
    public function __construct(array $subscribers = [])
    {
        if (!empty($subscribers)) {
            // Register given subscriber's.
            $this->subscribers = $subscribers;
            $this->addListeners($this->subscribers);
        }
    }
    
    /**
     * Add a listner.
     *
     * @param string $event
     *   The event name.
     * @param mixed $listener
     *   Either \League\Event\ListenerInterface or Callable listner.
     * @param int $priority
     *   Listner priority.
     *   
     * @return void
     */
    public function add($event, $listener, $priority = ListenerAcceptorInterface::P_NORMAL)
    {
        $subscriber = [
            $event => [
                'listener' => $listener,
                'priority' => $priority,
            ],
        ];
        $this->addListeners($subscriber);
    }

    /**
     * Magic method to call each public method from available emitter.
     *
     * @param string $method
     *   The name of the public method from Emitter class.
     * @param mixed $parameters
     *   The arguments passed to the method.
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->getEmitter()->$method(...$parameters);
    }
    
    /**
     * Internal method to add multiple listners.
     *
     * @param array $subscribers
     *   An array containing event subscribers.
     */
    protected function addListeners(array $subscribers = [])
    {
        if (!empty($subscribers)) {
            foreach ($subscribers as $event => $subscriber) {
                if (in_array('League\Event\ListenerInterface', class_implements($subscriber['listener']))) {
                    // If this is a class then it should implement ListenerInterface.
                    $listener = new $subscriber['listener']();
                } elseif (is_callable($subscriber['listener'])) {
                    // If this is a callable then check.
                    $listener = CallbackListener::fromCallable($subscriber['listener']);
                } else {
                    throw new \InvalidArgumentException(sprintf("Subscriber '%s' is not properly defined.", $event));
                }
                $priority = (array_key_exists('priority', $subscriber) ? $subscriber['priority'] : ListenerAcceptorInterface::P_NORMAL);
                $this->addListener($event, $listener, $priority);
            }
        }
    }
}
