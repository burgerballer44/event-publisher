<?php

use Burger\Event\EventPublisher;

$filesFirectory = __DIR__ . '/../';

// remove all events before each test
beforeEach(function () {
    $this->counter = 0;
    $this->eventPublisher = EventPublisher::instance();
    $this->eventPublisher->offAll();
    $this->filesFirectory = __DIR__ . '/../';
});

it('can add a function event handler with an event', function () {
    $event = 'login';
    $handler = 'trim';
   
    // there should be no handlers for the event
    expect($this->eventPublisher->hasEventHandlers($event))->toBeFalse;
   
    // attach a handler to the event
    $this->eventPublisher->on($event, $handler);
   
    // there should be a handler attached to the event now
    expect($this->eventPublisher->hasEventHandlers($event))->toBeTrue;
    expect($this->eventPublisher->getEventHandlers())->toHaveKey($event);
});

it('can add a file function event handler with an event', function() {
    $event = 'login';
    $handler = [$this->filesFirectory . '/Files/globalFunctionFile.php', 'doStuffGlobalFunction'];

    // there should be no handlers for the event
    expect($this->eventPublisher->hasEventHandlers($event))->toBeFalse;

    // attach a handler to the event
    $this->eventPublisher->on($event, $handler);

    // there should be a handler attached to the event now
    expect($this->eventPublisher->hasEventHandlers($event))->toBeTrue;
    expect($this->eventPublisher->getEventHandlers())->toHaveKey($event);
});

it('can add an object method event handler with an event', function() {
    $event = 'login';
    require_once $this->filesFirectory . '/Files/ClassWithMethod.php';
    $test = new \SampleClass();
    $handler = [$test, 'doStuffClassMethod'];

    // there should be no handlers for the event
    expect($this->eventPublisher->hasEventHandlers($event))->toBeFalse;

    // attach a handler to the event
    $this->eventPublisher->on($event, $handler);

    // there should be a handler attached to the event now
    expect($this->eventPublisher->hasEventHandlers($event))->toBeTrue;
    expect($this->eventPublisher->getEventHandlers())->toHaveKey($event);
});

it('can add a closure event handler with an event', function() {
    $event = 'login';
    $handler = function() {};

    // there should be no handlers for the event
    expect($this->eventPublisher->hasEventHandlers($event))->toBeFalse;

    // attach a handler to the event
    $this->eventPublisher->on($event, $handler);

    // there should be a handler attached to the event now
    expect($this->eventPublisher->hasEventHandlers($event))->toBeTrue;
    expect($this->eventPublisher->getEventHandlers())->toHaveKey($event);
});

it('cannot add a function that does not exist', function() {
    $event = 'login';
    $handler = 'doesNotExist';

    // attemp to attach a handler to the event
    $this->eventPublisher->on($event, $handler);
})->throws(Exception::class, "The global function doesNotExist does not exist.");

it('cannot add a function if file does not exist', function() {
    $event = 'login';
    $handler = [$this->filesFirectory . '/fileDoesNotExist.php', 'functionName'];

    // attemp to attach a handler to the event
    $this->eventPublisher->on($event, $handler);
})->throws(Exception::class, "The file {$filesFirectory}" . '/fileDoesNotExist.php' . " does not exist.");

it('cannot add a function if file exists but function does not', function() {
    $event = 'login';
    $handler = [$this->filesFirectory . '/Files/globalFunctionFile.php', 'functionNotExist'];

    // attemp to attach a handler to the event
    $this->eventPublisher->on($event, $handler);
})->throws(Exception::class, "The function functionNotExist in file {$filesFirectory}" . '/Files/globalFunctionFile.php' . " does not exist.");

it('cannot add a method if object is not callable', function() {
    require_once $this->filesFirectory . '/Files/ClassWithMethod.php';
    $event = 'login';
    $test = new \SampleClass();

    $handler = [$test, 'methodNotExist'];

    // attemp to attach a handler to the event
    $this->eventPublisher->on($event, $handler);
})->throws(Exception::class, "The object is not callable.");

it('can append a handler to end of list and insert to beginning of list', function() {
    $event = 'login';
    $handler = 'trim';
    $handlerToAppend = 'sort';
    $handlerToInsertToBeginning = 'usort';

    // attach handlers to the event
    $this->eventPublisher->on($event, $handler);
    $this->eventPublisher->on($event, $handlerToAppend);
    $this->eventPublisher->on($event, $handlerToInsertToBeginning, null, false);

    // get handlers for the event
    $handlers = $this->eventPublisher->getEventHandlers()[$event];

    expect($handlerToInsertToBeginning)->toBe($handlers[0][0]);
    expect($handler)->toBe($handlers[1][0]);
    expect($handlerToAppend)->toBe($handlers[2][0]);
});

it('an event handler can be removed from an event', function() {
    $event = 'login';
    $handler = 'trim';

    // attach handlers to the event
    $this->eventPublisher->on($event, $handler);

    // there should be one event
    expect($this->eventPublisher->hasEventHandlers($event))->toBeTrue;

    // remove the event
    $this->eventPublisher->off($event, $handler);

    expect($this->eventPublisher->hasEventHandlers($event))->toBeFalse;
});

it('all event handlers can be removed', function() {
    $event = 'login';
    $handler = 'trim';

    // attach handlers to the event
    $this->eventPublisher->on($event, $handler);

    // there should be one event
    expect($this->eventPublisher->getEventHandlers())->toHaveCount(1);

    // remove all events
    $this->eventPublisher->offAll();

    expect($this->eventPublisher->getEventHandlers())->toHaveCount(0);
});

it('can add subscriber events', function() {
    $event = 'login';

    require_once $this->filesFirectory . '/Files/SampleSubscriber.php';
    $subscriber = new \SampleSubscriber();

    // there should be no handlers for the event
    expect($this->eventPublisher->hasEventHandlers($event))->toBeFalse;

    // attach subscriber event handlers
    $this->eventPublisher->subscribe($subscriber);

    // there should be a handler attached to the event now
    expect($this->eventPublisher->hasEventHandlers($event))->toBeTrue;
});

it('all event hanlders for event will bepublished by event', function() {
    $event = 'login';

    $handler1 = function($event) { 
        $data = $event->getData();
        $data->counter++;
    };

    $handler2 = [$this->filesFirectory . '/Files/globalFunctionFile.php', 'doStuffGlobalFunction'];

    require_once $this->filesFirectory . '/Files/ClassWithMethod.php';
    $handler3 = [new \SampleClass(), 'doStuffClassMethod'];

    require_once $this->filesFirectory . '/Files/SampleSubscriber.php';
    $subscriber = new \SampleSubscriber();

    $this->eventPublisher->on('*', $handler1);
    $this->eventPublisher->publish($event, $this);
    // counter + wildcard
    expect($this->counter)->toBe(1); // 0 + 1

    $this->eventPublisher->on($event, $handler2);
    $this->eventPublisher->publish($event, $this);
    // counter + wildcard + global function 
    expect($this->counter)->toBe(3); // 1 + 1 + 1

    $this->eventPublisher->on($event, $handler3);
    $this->eventPublisher->publish($event, $this);
    // counter + wildcard + global function + object method 
    expect($this->counter)->toBe(6); // 3 + 1 + 1 +1

    // this subscriber attaches 2 more handlers
    $this->eventPublisher->subscribe($subscriber);
    // counter + wildcard + global function + object method + 2 subscriber methods 
    $this->eventPublisher->publish($event, $this);
    expect( $this->counter)->toBe(11); // 6 + 1 + 1 + 1 + 1 + 1
});

it('wildcard event is published by multiple events', function() {
    $event1 = 'login';
    $event2 = 'burger';

    $handler = function($event) { 
        $data = $event->getData();
        $data->counter++;
    };

    // attach the handler to the wildcard
    $this->eventPublisher->on('*', $handler);

    // publish the first event
    $this->eventPublisher->publish($event1, $this);
    expect($this->counter)->toBe(1);

    // publish the second event
    $this->eventPublisher->publish($event2, $this);
    expect($this->counter)->toBe(2);
});
