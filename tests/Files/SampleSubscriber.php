<?php

use Burger\Event\EventPublisherInterface;
use Burger\Event\EventSubscriberInterface;

class SampleSubscriber implements EventSubscriberInterface
{
    public function doStuffOnLoginForSubscriber($event)
    {
        $data = $event->getData();
        $data->counter++;
    }

    public function doSomethingElseOnLoginForSubscriber($event)
    {
        $data = $event->getData();
        $data->counter++;
    }

    public function subscribe(EventPublisherInterface $eventService)
    {
        $eventService->on('login', [$this, 'doStuffOnLoginForSubscriber']);
        $eventService->on('login', [$this, 'doSomethingElseOnLoginForSubscriber']);
    }
}