<?php

namespace OpenAI\Laravel\Events;

use Illuminate\Contracts\Events\Dispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;

class DispatcherDecorator implements EventDispatcherInterface
{
    public function __construct(
        private readonly Dispatcher $events
    ) {
    }

    public function dispatch(object $event)
    {
        return (object) $this->events->dispatch($event);
    }
}
