<?php

namespace Burger\Event;

interface EventPublisherInterface
{
    public static function on(string $name, $handler, $data = null, $append = true);
    public function subscribe(EventSubscriberInterface $subscriber);
    public static function publish(string $name, $data = null);
}
