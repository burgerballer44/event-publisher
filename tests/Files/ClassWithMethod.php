<?php

class SampleClass
{
    public function doStuffClassMethod($event)
    {
        $data = $event->getData();
        $data->counter++;;
    }
}