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
     * @param array $subscribers
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
     * Add a listener.
     *
     * @param string $event
     *   The event name.
     * @param mixed $listener
     *   Either \League\Event\ListenerInterface or Callable listener.
     * @param int $priority
     *   Listener priority.
     *   
     * @return void
     */
    public function add($event, $listener, $priority = ListenerAcceptorInterface::P_NORMAL)
    {
        // Create an array of event subscribers to pass to the main addition method.
        $subscriber = [
            $event => [
                [$listener, $priority]
            ]
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
     * Internal method to add multiple listeners.
     *
     * @param array $subscribers
     *   An array containing event subscribers.
     */
    protected function addListeners(array $subscribers = [])
    {
        if (!empty($subscribers)) {
            foreach ($subscribers as $event => $listeners) {
                if (!empty($listeners)) {
                    foreach ($listeners as $listener) {
                        // First array key is the listener and the second is the priority.
                        if (!array_key_exists(0, $listener)) {
                            throw new \InvalidArgumentException('Listeners should be provided as the first element of the array.');
                        }
                        // If the class name is given then that should be string and should exist.
                        if (is_string($listener[0]) && class_exists($listener[0]) &&
                            in_array('League\Event\ListenerInterface', class_implements($listener[0]))) {
                            $eventListener = new $listener[0]();
                        } elseif (is_callable($listener[0])) {
                            $eventListener = CallbackListener::fromCallable($listener[0]);
                        } else {
                            throw new \InvalidArgumentException(sprintf("Subscriber for event '%s' is not properly defined.", $event));
                        }
                        $priority = (array_key_exists(1, $listener) ? $listener[1] : ListenerAcceptorInterface::P_NORMAL);
                        $this->addListener($event, $eventListener, $priority);
                    }
                } else {
                    // If there is an array of listeners then it can't be empty.
                    throw new \InvalidArgumentException('At least one listener has to be provided.');
                }
            }
        }
    }
}
