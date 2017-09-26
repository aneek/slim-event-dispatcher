<?php

namespace Slim\Event\Tests;

use League\Event\AbstractListener;
use League\Event\EventInterface;

class TestListner extends AbstractListener
{

    /**
     * A response that is set via the handle method.
     *
     * @var null
     */
    private $response = null;

    /**
     * {@inheritdoc}
     */
    public function handle(EventInterface $event, $param = null)
    {
        $this->response = $param;
    }

    public function getResponse()
    {
        return $this->response;
    }
}

