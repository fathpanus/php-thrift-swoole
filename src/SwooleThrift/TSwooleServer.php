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
        $default = [
            'worker_num'            => 2,
            'daemonize'             => true,
            'dispatch_mode'         => 1,
            'http_server_port' => 8090,
            'http_server_host' => '127.0.0.1'
        ];
        $setting = array_merge($default, $this->transport_->getSetting());
        $httpServer = new \swoole_http_server($setting['http_server_host'], $setting['http_server_port']);
        $httpServer->set($setting);
        //server status page
        $httpServer->on('request', function(\swoole_http_request $request, \swoole_http_response $response) use($httpServer){
            $status = $httpServer->stats();
            $response->header('Content-type', 'application/json');
            $response->write(json_encode($status));
        });

        $tcpServer = $httpServer->addListener($this->transport_->getHost(),
            $this->transport_->getPort(),
            SWOOLE_SOCK_TCP
        );
        $tcpServer->on('Receive', [$this, 'handleRequest']);
        $httpServer->on('ManagerStart', function() {
            //osx warning
            @swoole_set_process_name('thrift_server_swoole_manager');
        });
        $httpServer->on('WorkerStart', function() {
            @swoole_set_process_name('thrift_server_swoole_worker');
        });

        // 数据包格式：  pack('N', body_length) + body
        $tcpSetting['open_length_check'] = true;
        $tcpSetting['package_length_type']   = 'N';
        $tcpSetting['package_length_offset']   = 0;
        $tcpSetting['package_body_offset']   = 4;
        $tcpServer->set($tcpSetting);
        $this->server = $httpServer;
        $httpServer->start();
    }

    public function handleRequest(\swoole_server $server, $fd, $reactorId, $data)
    {
        /** @var TSwooleTransport $transport */
//        /** @var FrameTransport $transport */
        $transport = $this->transport_->accept();
        $transport->setServer($server);
        $transport->setNetFD($fd);
        $transport->setData($data);
//        $transport->setHandle($fd);
//        $transport->server = $server;
//        $transport->buffer = $data;
        $inputTransport = $this->inputTransportFactory_->getTransport($transport);
        $outputTranport = $this->outputTransportFactory_->getTransport($transport);
        $inputProtocol = $this->inputProtocolFactory_->getProtocol($inputTransport);
        $outputProtocol = $this->outputProtocolFactory_->getProtocol($outputTranport);
        try {
            $this->processor_->process($inputProtocol, $outputProtocol);
        } catch (\Exception $e) {
            $log = "remote call error: " . $e->getCode() . '--msg:' . $e->getMessage() . PHP_EOL. $e->getTraceAsString();
            echo $log;
        }
//        $this->server->close($fd);
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