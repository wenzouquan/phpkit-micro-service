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
        $this->className = $serverName;
        if(is_array($this->serverips) && !empty($this->serverips)){
            $key = array_rand($this->serverips, 1); //随机找到一个服务
            $this->ipAndPort = explode(":",$this->serverips[$key]);
        }
        if($ip && $port){
             $this->ipAndPort = [$ip,$port];
             $this->useTcp=1;
        }
        return $this;
    }

    function __call($name, $arguments)
    {
        $params=array(
            'className'=>$this->className,
            'method'=>$name,
            'arguments'=>$arguments
        );
       // var_dump($this->service);
        //exit();
        if($this->useTcp){
             $this->service = $this->client->getRPCService("Services\\Demo\\HiService",$this->ipAndPort[0],$this->ipAndPort[1]);
            $returnMsg= json_decode($this->service->say(json_encode($params)),true);
        }else if(!empty($this->className) && class_exists($this->className) && method_exists(new $this->className(),$name)){
            $returnMsg= call_user_func_array(array(new $this->className(), $name), $arguments);//如果服务在本地
        }else if( !empty($this->ipAndPort)){
            $this->service = $this->client->getRPCService("Services\\Demo\\HiService",$this->ipAndPort[0],$this->ipAndPort[1]);
            //var_dump($params);
            $returnMsg= json_decode($this->service->say(json_encode($params)),true);
        }else{
            throw new \Exception($this->className ." 类未定义 ", 1);
            
        }
        return $returnMsg;
    }


}

