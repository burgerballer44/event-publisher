<?php

function doStuffGlobalFunction($event)
{
    $data = $event->getData();
    $data->counter++;
}