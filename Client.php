<?php
namespace phpkit\microservice;
class Client{
    protected $service;
    function __construct($serverips=array())
    {
        $this->serverips=$serverips;
        $servicesApi = array(
            'Services\\Demo' => dirname(__FILE__) . '/service_api/gen-php', //Services的目录
        );
        $this->client = new \phpkit\thriftrpc\Client($servicesApi);
    }

    function getService($serverName="",$ip="",$port=""){
        $key = array_rand($this->serverips, 1); //随机找到一个服务
        $ipAndPort = explode(":",$this->serverips[$key]);
        $this->service = $this->client->getRPCService("Services\\Demo\\HiService",$ipAndPort[0],$ipAndPort[1]);
        $this->className = $serverName;
        return $this;
    }

    function __call($name, $arguments)
    {
        $params=array(
            'className'=>$this->className,
            'method'=>$name,
            'arguments'=>$arguments
        );
        if(class_exists($this->className)){
            $returnMsg= call_user_func_array(array(new $this->className(), $name), $arguments);//如果服务在本地
        }else{
            $returnMsg= json_decode($this->service->say(json_encode($params)),true);
        }

        return $returnMsg;
    }


}
