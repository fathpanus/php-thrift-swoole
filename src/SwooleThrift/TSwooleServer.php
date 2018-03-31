<?php

use Thrift\Server\TServer;

/**
 * Class TSwooleServer
 * @property TSwooleServerTransport $transport_
 */
class TSwooleServer extends TServer
{
    protected $server;

    /**
     * Serves the server. This should never return
     * unless a problem permits it to do so or it
     * is interrupted intentionally
     *
     * @return void
     */
    public function serve()
    {
        // TODO: Implement serve() method.
//        $this->transport_->listen();
        $this->server = new \swoole_server($this->transport_->getHost(),
            $this->transport_->getPort()
            );
        // 数据包格式：  pack('N', body_length) + body
        $default = [
            'worker_num'            => 2,
            'daemonize'             => true,
            'dispatch_mode'         => 1, //1: 轮循, 3: 争抢
            'open_length_check'     => true, //打开包长检测
            'package_length_type'   => 'N', //长度的类型，参见PHP的pack函数
            'package_length_offset' => 0,   //第N个字节是包长度的值
            'package_body_offset'   => 4,   //从第几个字节计算长度
        ];
        $setting = array_merge($default, $this->transport_->getSetting());
        $this->server->set($setting);
    }

    public function handleRequest(\swoole_server $server, $fd, $reactorId, $data)
    {
        /** @var TSwooleTransport $transport */
        $transport = $this->transport_->accept();
        $transport->setServer($server);
        $transport->setNetFD($fd);
        $transport->setData($data);
        $inputTransport = $this->inputTransportFactory_->getTransport($transport);
        $outputTranport = $this->outputTransportFactory_->getTransport($transport);
        $inputProtocol = $this->inputProtocolFactory_->getProtocol($inputTransport);
        $outputProtocol = $this->outputProtocolFactory_->getProtocol($outputTranport);
        try {
            $this->processor_->process($inputProtocol, $outputProtocol);
        } catch (\Exception $e) {
            $log = "code: " . $e->getCode() . '--msg:' . $e->getMessage() . "\r\n" . $e->getTraceAsString();
            echo $log;
        }
        $this->server->close($fd);
    }

    protected function registerEvent()
    {
        $this->server->on('Receive', [$this, 'handleRequest']);
    }

    /**
     * Stops the server serving
     *
     * @return void
     */
    public function stop()
    {
        // TODO: Implement stop() method.
        $this->server->shutdown();
    }
}