<?php

namespace Burger\Event;

interface EventSubscriberInterface
{
    public function subscribe(EventPublisherInterface $publisher);
}
