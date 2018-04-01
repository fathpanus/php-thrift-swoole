<?php

$service = new YourServiceImpl();
$processor = new YourServiceProcessor($service);

$setting = [
    'log_file' => __DIR__.'/swoole.log',
    'pid_file' => __DIR__.'/thrift.pid',
];
$socket_tranport = new \SwooleThrift\TSwooleServerTransport('0.0.0.0', 8192, $setting);
$out_factory = $in_factory = new Thrift\Factory\TTransportFactory();
$out_protocol = $in_protocol = new Thrift\Factory\TBinaryProtocolFactory();

$server = new \SwooleThrift\TSwooleServer($processor, $socket_tranport, $in_factory, $out_factory, $in_protocol, $out_protocol);
$server->serve();


