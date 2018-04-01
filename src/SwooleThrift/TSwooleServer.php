<?php
namespace SwooleThrift;

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
        // 数据包格式：  pack('N', body_length) + body
        $default = [
            'worker_num'            => 2,
            'daemonize'             => true,
            'dispatch_mode'         => 1,
            'open_length_check'     => true,
            'package_length_type'   => 'N',
            'package_length_offset' => 0,
            'package_body_offset'   => 4,
            'http_server_port' => 8090,
        ];
        $setting = array_merge($default, $this->transport_->getSetting());
        $httpServer = new \swoole_http_server($this->transport_->getHost(), $setting['http_server_port']);
        $this->server = $httpServer->addListener($this->transport_->getHost(),
            $this->transport_->getPort(),
            SWOOLE_SOCK_TCP
        );
        //server status page
        $httpServer->on('request', function(swoole_http_request $request, swoole_http_response $response) use($httpServer){
            $status = $httpServer->stats();
            $response->header('Content-type', 'application/json');
            $response->write(json_encode($status));
        });
//        $this->server = new \swoole_server($this->transport_->getHost(),
//            $this->transport_->getPort()
//            );
        $this->server->set($setting);
        $this->registerEvent();
        $this->server->start();
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
            $log = "remote call error: " . $e->getCode() . '--msg:' . $e->getMessage() . "\r\n" . $e->getTraceAsString();
            echo $log;
        }
        $this->server->close($fd);
    }

    protected function registerEvent()
    {
        $this->server->on('Receive', [$this, 'handleRequest']);
        $this->server->on('ManagerStart', function() {
            swoole_set_process_name('thrift_server_swoole_master');
        });
        $this->server->on('WorkerStart', function() {
            swoole_set_process_name('thrift_server_swoole_worker');
        });
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