<?php
namespace phpkit\microservice;
class Client{
    protected $service;
    protected $serviceClients;
    function __construct($serverips=array())
    {
        $this->serverips=$serverips;
        $servicesApi = array(
            'Services\\Demo' => dirname(__FILE__) . '/service_api/gen-php', //Services的目录
        );
        $this->client = new \phpkit\thriftrpc\Client($servicesApi);
    }

    function get($serverName="",$ip="",$port=""){
        return $this->getService($serverName,$ip,$port);
    }
    function getService($serverName="",$ip="",$port=""){
        $serviceClient = new self();
        $serviceClient->className = $serverName;
        if(is_array($serviceClient->serverips) && !empty($serviceClient->serverips)){
            $key = array_rand($serviceClient->serverips, 1); //随机找到一个服务
            $serviceClient->ipAndPort = explode(":",$serviceClient->serverips[$key]);
        }
        if($ip && $port){
             $serviceClient->ipAndPort = [$ip,$port];
             $serviceClient->useRpc=1;
        }
        return $serviceClient;
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
        if(isset($this->useRpc)){
             $this->service = $this->client->getRPCService("Services\\Demo\\HiService",$this->ipAndPort[0],$this->ipAndPort[1]);
            $returnMsg= json_decode($this->service->say(json_encode($params)),true);
        }else if(!empty($this->className) && class_exists($this->className) && method_exists(new $this->className(),$name)){
            $returnMsg= call_user_func_array(array(new $this->className(), $name), $arguments);//如果服务在本地
        }else if( !empty($this->ipAndPort)){
            $this->service = $this->client->getRPCService("Services\\Demo\\HiService",$this->ipAndPort[0],$this->ipAndPort[1]);
            //var_dump($params);
            $returnMsg= json_decode($this->service->say(json_encode($params)),true);
        }else{
            if(!class_exists($this->className)){
                $error = $this->className ." 类未定义 ";
            }else if(!method_exists(new $this->className(),$name)){
                $error = $this->className ."中 {$name} 方法未定义 ";
            }

            throw new \Exception($error, 1);
            
        }
        return $returnMsg;
    }


}

