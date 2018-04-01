<?php

$socket = new \Thrift\Transport\TSocket('192.168.10.200', 8100);
$transport = new \Thrift\Transport\TFramedTransport($socket);
$protocol = new \Thrift\Protocol\TBinaryProtocol($transport);
$transport->open();

$client = new YourServiceClient($protocol);
$client->implMethod(...);

$client->close();
