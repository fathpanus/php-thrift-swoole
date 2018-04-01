# 基于Swoole的Thrift Server实现


## 安装
```
composer require panus/thrift-php-swoole
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

## 注意事项
* 由于传输层是用`TFramedTransport`，所以对应的客户端也是要采用该传输层


## 参考部分
> [swoole/thrift-rpc-server](https://github.com/swoole/thrift-rpc-server)