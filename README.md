# 基于Swoole的Thrift Server实现


## 安装
```
composer require panus/php-thrift-swoole
```

## 服务端示例
* 生成服务端代码
```
thrift --gen php:server,psr4 order.thrift
```
* 业务层自行去实现生成的接口
```
$service = new OrderServiceImpl();
$processor = new OrderServiceProcessor($service);

$setting = [
    
    'log_file' => __DIR__.'/swoole.log',
    'pid_file' => __DIR__.'/thrift.pid',
];
$socket_tranport = new \SwooleThrift\TSwooleServerTransport('0.0.0.0', 8192, $setting);
$out_factory = $in_factory = new Thrift\Factory\TTransportFactory();
$out_protocol = $in_protocol = new Thrift\Factory\TBinaryProtocolFactory();

$server = new \SwooleThrift\TSwooleServer($processor, $socket_tranport, $in_factory, $out_factory, $in_protocol, $out_protocol);
$server->serve();
```

## 客户端示例
``` 
$socket = new \Thrift\Transport\TSocket('192.168.0.101', 8100);
$transport = new \Thrift\Transport\TFramedTransport($socket);
$protocol = new \Thrift\Protocol\TBinaryProtocol($transport);
$transport->open();

$client = new OrderServiceClient($protocol);
$client->implMethod(...);

$client->close();
```


## 开启thrift_protocol扩展（可选）
```
cd /thrift_root/lib/php/src/ext/thrift_protocol
/php_path/bin/phpize
./configure --with-php-config=/php_path/bin/php-config
make
make install
echo "extension=thrift_protocol.so" >> /path/php.ini
```

## 后端服务负载均衡
* nginx从1.9.0后引入模块ngx_stream_core_module，模块默认是没有开启的，编译时开启 --with-stream
```
http {
  ...
}
stream {
    upstream thrift {
        server 127.0.0.1:8192 weight=1;
        #server backend1:9000 weight=5;
    }   
    server {
        listen 8100;
        proxy_connect_timeout 1s;
        proxy_timeout 3s;
        proxy_pass thrift;
    }   
}
```

## Server状态参数输出
* 默认绑定的本地回环地址，端口为8090,可在setting 里设置`http_server_host`和`http_server_port`，不建绑在公网ip地址上
* 响应如下，也就是`swoole_server->stats`
``` 
{
    "start_time": 1522580115,
    "connection_num": 2,
    "accept_count": 2,
    "close_count": 0,
    "tasking_num": 0,
    "request_count": 0,
    "worker_request_count": 0
}
```

## 注意事项
* 由于传输层是用`TFramedTransport`，所以对应的客户端也是要采用该传输层


## 参考部分
> [swoole/thrift-rpc-server](https://github.com/swoole/thrift-rpc-server)