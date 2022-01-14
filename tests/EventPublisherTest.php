<?php

use Burger\Event\EventPublisher;
use PHPUnit\Framework\TestCase;

class EventPublisherTest extends TestCase
{
    public $counter = 0;
    private $eventPublisher;

    // remove all events before each test
    public function setUp()
    {
        $this->counter = 0;
        $this->eventPublisher = EventPublisher::instance();
        $this->eventPublisher->offAll();
    }

    public function testItCanAddAFunctionEventHandlerWithAnEvent()
    {
        $event = 'login';
        $handler = 'trim';

        // there should be no handlers for the event
        $this->assertFalse($this->eventPublisher->hasEventHandlers($event));

        // attach a handler to the event
        $this->eventPublisher->on($event, $handler);

        // there should be a handler attached to the event now
        $this->assertTrue($this->eventPublisher->hasEventHandlers($event));
        $this->assertArrayHasKey($event, $this->eventPublisher->getEventHandlers());
    }

    public function testItCanAddAFileFunctionEventHandlerWithAnEvent()
    {
        $event = 'login';
        $handler = [__DIR__ . '/Files/globalFunctionFile.php', 'doStuffGlobalFunction'];

        // there should be no handlers for the event
        $this->assertFalse($this->eventPublisher->hasEventHandlers($event));

        // attach a handler to the event
        $this->eventPublisher->on($event, $handler);

        // there should be a handler attached to the event now
        $this->assertTrue($this->eventPublisher->hasEventHandlers($event));
        $this->assertArrayHasKey($event, $this->eventPublisher->getEventHandlers());
    }

    public function testItCanAddAnObjectMethodEventHandlerWithAnEvent()
    {
        $event = 'login';
        require_once __DIR__ . '/Files/ClassWithMethod.php';
        $test = new \SampleClass();
        $handler = [$test, 'doStuffClassMethod'];

        // there should be no handlers for the event
        $this->assertFalse($this->eventPublisher->hasEventHandlers($event));

        // attach a handler to the event
        $this->eventPublisher->on($event, $handler);

        // there should be a handler attached to the event now
        $this->assertTrue($this->eventPublisher->hasEventHandlers($event));
        $this->assertArrayHasKey($event, $this->eventPublisher->getEventHandlers());
    }

    public function testItCanAddAClosureEventHandlerWithAnEvent()
    {
        $event = 'login';
        $handler = function() {};

        // there should be no handlers for the event
        $this->assertFalse($this->eventPublisher->hasEventHandlers($event));

        // attach a handler to the event
        $this->eventPublisher->on($event, $handler);

        // there should be a handler attached to the event now
        $this->assertTrue($this->eventPublisher->hasEventHandlers($event));
        $this->assertArrayHasKey($event, $this->eventPublisher->getEventHandlers());
    }

    public function testItCannotAddAFunctionThatDoesNotExist()
    {
        $event = 'login';
        $handler = 'doesNotExist';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The global function {$handler} does not exist.");

        // attemp to attach a handler to the event
        $this->eventPublisher->on($event, $handler);
    }

    public function testItCannotAddAFunctionIfFileDoesNotExist()
    {
        $event = 'login';
        $handler = [__DIR__ . '/fileDoesNotExist.php', 'functionName'];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The file {$handler[0]} does not exist.");

        // attemp to attach a handler to the event
        $this->eventPublisher->on($event, $handler);
    }

    public function testItCannotAddAFunctionIfFileExistsButFunctionDoesNot()
    {
        $event = 'login';
        $handler = [__DIR__ . '/Files/globalFunctionFile.php', 'functionNotExist'];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The function {$handler[1]} in file {$handler[0]} does not exist.");

        // attemp to attach a handler to the event
        $this->eventPublisher->on($event, $handler);
    }

    public function testItCannotAddAMethodIfObjectIsNotCallable()
    {
        require_once __DIR__ . '/Files/ClassWithMethod.php';
        $event = 'login';
        $test = new \SampleClass();

        $handler = [$test, 'methodNotExist'];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The object is not callable.");

        // attemp to attach a handler to the event
        $this->eventPublisher->on($event, $handler);
    }

    public function testItCanAppendAHandlerToEndOfListAndInsertToBeginningOfList()
    {
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

        $this->assertEquals($handlerToInsertToBeginning, $handlers[0][0], 'failed to insert handler to beginning');
        $this->assertEquals($handler, $handlers[1][0]);
        $this->assertEquals($handlerToAppend, $handlers[2][0]);
    }

    public function testAnEventHandlerCanBeRemovedFromAnEvent()
    {
        $event = 'login';
        $handler = 'trim';

        // attach handlers to the event
        $this->eventPublisher->on($event, $handler);

        // there should be one event
        $this->assertTrue($this->eventPublisher->hasEventHandlers($event));

        // remove the event
        $this->eventPublisher->off($event, $handler);

        $this->assertFalse($this->eventPublisher->hasEventHandlers($event));
    }

    public function testAllEventHandlersCanBeRemoved()
    {
        $event = 'login';
        $handler = 'trim';

        // attach handlers to the event
        $this->eventPublisher->on($event, $handler);

        // there should be one event
        $this->assertCount(1, $this->eventPublisher->getEventHandlers());

        // remove all events
        $this->eventPublisher->offAll();

        $this->assertCount(0, $this->eventPublisher->getEventHandlers());
    }

    public function testItCanAddSubscriberEvents()
    {
        $event = 'login';

        require_once __DIR__ . '/Files/SampleSubscriber.php';
        $subscriber = new \SampleSubscriber();

        // there should be no handlers for the event
        $this->assertFalse($this->eventPublisher->hasEventHandlers($event));

        // attach subscriber event handlers
        $this->eventPublisher->subscribe($subscriber);

        // there should be a handler attached to the event now
        $this->assertTrue($this->eventPublisher->hasEventHandlers($event));
    }

    public function testAllEventHanldersForEventWillBepublishedByEvent()
    {
        $event = 'login';

        $handler1 = function($event) { 
            $data = $event->getData();
            $data->counter++;
        };

        $handler2 = [__DIR__ . '/Files/globalFunctionFile.php', 'doStuffGlobalFunction'];

        require_once __DIR__ . '/Files/ClassWithMethod.php';
        $handler3 = [new \SampleClass(), 'doStuffClassMethod'];

        require_once __DIR__ . '/Files/SampleSubscriber.php';
        $subscriber = new \SampleSubscriber();

        $this->eventPublisher->on('*', $handler1);
        $this->eventPublisher->publish($event, $this);
        // counter + wildcard
        $this->assertEquals(1, $this->counter); // 0 + 1

        $this->eventPublisher->on($event, $handler2);
        $this->eventPublisher->publish($event, $this);
        // counter + wildcard + global function 
        $this->assertEquals(3, $this->counter); // 1 + 1 + 1

        $this->eventPublisher->on($event, $handler3);
        $this->eventPublisher->publish($event, $this);
        // counter + wildcard + global function + object method 
        $this->assertEquals(6, $this->counter); // 3 + 1 + 1 +1

        // this subscriber attaches 2 more handlers
        $this->eventPublisher->subscribe($subscriber);
        // counter + wildcard + global function + object method + 2 subscriber methods 
        $this->eventPublisher->publish($event, $this);
        $this->assertEquals(11, $this->counter); // 6 + 1 + 1 + 1 + 1 + 1
    }

    public function testWildcardEventIsPublishedByMultipleEvents()
    {
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
        $this->assertEquals(1, $this->counter);

        // publish the second event
        $this->eventPublisher->publish($event2, $this);
        $this->assertEquals(2, $this->counter);
    }
}