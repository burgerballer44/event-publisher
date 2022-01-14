# Event Publisher

## Events and Event Handlers

The event publisher allows you to inject custom code at specific points in time. You can attach this custom code to an event so that when the event is published, the code gets executed automatically.

Event handlers are callbacks that get executed when the event it is attached to is published. The 4 accepted callbacks are:

- a global function specified as a string (without parenthesis), e.g., `'trim'`;
- an object method specified as an array of an object and a method name as a string (without parentheses), e.g., `[$object, 'methodName']`;
- an anonymous function, e.g., `function($event) { ... }`
- While not technically being a callable, a file path with a function is also acceptable, e.g., `['/the/file/path', 'functionName']`

The signature of an event handler is:
```
function ($event) {
    // $event is an object that contains $event->getName() and $event->getData()
}
```

## Attaching Event Handlers
You can attach a handler to an event by calling the EventPublisher::on() method. Please note that when adding a file path and function name as the callable, the file will be included and then the function added as if you were adding a global function.
```
// EventPublisher::instance() returns the singleton
$eventPublisher = EventPublisher::instance();

// this handler is a global function
$eventPublisher->on('eventName', 'functionName');

// this handler is a object method
$eventPublisher->on('eventName', [$object, 'methodName']);

// this handler is an anonymous function
$eventPublisher->on('eventName', function($event) { 
    // event handling logic
});

// this handler is a filepath with a function
$eventPublisher->on('eventName', ['/the/file/path', 'functionName']);
```

When attaching an event handler, you may provide additional data as the third parameter. The data will be made available to the handler when the event is published and the handler is called.
```
// The following code will display "abc" when the event is published
// because $event->getData() contains the data passed as the 3rd argument to "on"
$eventPublisher->on('eventName', 'functionName', 'abc');

function functionName($event) {
    echo $event->getData();
}
```
### Wildcard Global Event
Sometimes you may want to have a handler attached to all events. You can do this by using an asterisk as the event name.
```
$eventPublisher->on('*', 'functionName');
```

### Event Handler Order
By default, a newly attached handler is appended to the existing handler queue for the event. As a result, the handler will be called in the last place when the event is published. To insert the new handler at the start of the handler queue so that the handler gets called first, you may call EventPublisher::on(), passing false for the fourth parameter $append:
```
$eventPublisher->on('eventName, function ($event) {
    // ...
}, $data, false);
```

## Publishing Events
Events are published by calling the EventPublisher::publish() method. The method requires an event name, and optionally any data to be passed to the event handlers. For example:
```
$eventPublisher->publish('eventName');

// or with data
$eventPublisher->publish('eventName', ['Fee' => 'fi', 'fo'=> 'fum']);
```

## Detaching Event Handlers
To detach a handler from an event, call the EventPublisher::off() method. The EventPublisher::offAll() method can be used to remove all events.
```
// this handler is a global function
$eventPublisher->off('eventName', 'functionName');

// this handler is a object method
$eventPublisher->off('eventName', [$object, 'methodName']);

// this handler is an anonymous function
$eventPublisher->off('eventName', $anonymousFunction);

// removes all events
$eventPublisher->offAll();
```

## Subscribers
Event subscribers are classes that may subscribe to multiple events from within the class itself, allowing you to define several event handlers within a single class. Subscribers should define a subscribe method, which will be passed the event publisher instance. You may call the on() method on the given publisher to register the event handlers. Subscribers can subscribe to the event publisher with the EventPublisher::subscribe() method.
```
<?php

class UserEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function onUserLogin($event) {}

    /**
     * Handle user logout events.
     */
    public function onUserLogout($event) {}

    /**
     * Register the events and handlers for the subscriber.
     *
     * @param  EventPublisher $eventPublisher
     */
    public function subscribe($eventPublisher)
    {
        $eventPublisher->on('login', [$this, 'onUserLogin']);
        $eventPublisher->on('logout', [$this, 'onUserLogout']);
    }
}

// subscribe the subscriber with the sbscribe method
$eventPublisher->subscribe(new UserEventSubscriber);
```