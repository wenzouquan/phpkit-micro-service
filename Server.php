<?php
namespace phpkit\microservice;
class Server{
    protected $client;
    function __construct()
    {
        $servicesApi = array(
            'Services\\Demo' => dirname(__FILE__) . '/service_api/gen-php', //Services的目录
        );
        $this->client = new \phpkit\thriftrpc\Client($servicesApi);
    }

    function start($config=array()){
        $server = new bin\Server($config);
        $server->serve();
    }
}

