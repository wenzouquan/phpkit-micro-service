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

    // function get($serverName="",$ip="",$port=""){
    //     return $this->getService($serverName,$ip,$port);
    // }
    function getService($serverName="",$ip="",$port=""){
        $serviceClient = new self($this->serverips);
        $serviceClient->className = $serverName;
        if(is_array($serviceClient->serverips) && !empty($serviceClient->serverips)){
            $serviceClient->ipAndPort = $this->randIp();
        }
        if($ip && $port){
             $serviceClient->ipAndPort = [$ip,$port];
             $serviceClient->useRpc=1;
        }
        return $serviceClient;
    }
//随机取一个ip
     function randIp($disableIps=[]){
        $find=1;
        $ip="";
        $i=1;
        while ($find==1) {
            $key = array_rand($this->serverips, 1); //随机找到一个服务
            $ip = $this->serverips[$key];
            if(!in_array($ip, $disableIps)){
                $find =0;
            }
            if($i==count($this->serverips)){
                 $find =0;
            }
            $i++;
        }
        $ipAndPort = explode(":",$ip);
        return $ipAndPort;
     }
    //类中所有属性
     function getVars(){
         $class_vals = get_object_vars($this);
         return $class_vals ;
     }

    function __call($name, $arguments)
    {
        $params=array(
            'className'=>$this->className,
            'method'=>$name,
            'arguments'=>$arguments
        );
        //$class_vals = get_class_vars(get_class($this));
        $class_vals = $this->getVars();
        $params['vals']=$class_vals;
        \phpkit\microservice\args()->set($params);
        if(isset($this->useRpc)){
            $this->service = $this->client->getRPCService("Services\\Demo\\HiService",$this->ipAndPort[0],$this->ipAndPort[1]);
            $returnMsg= json_decode($this->service->say(json_encode($params)),true);
        }else if(!empty($this->className) && class_exists($this->className) && method_exists(new $this->className(),$name)){
            $obj = new $this->className();
            foreach($class_vals as $k=>$v){
                $obj->$k = $v;
            }
            $returnMsg= call_user_func_array(array($obj, $name), $arguments);//如果服务在本地
        }else if( !empty($this->ipAndPort)){
           //使用tcp服务
            $disableIps=[];//异常ip
            $tryCount = 5;
            $findService=1;
            $hasError = 0 ;
            $allError=[];
            //在ip内查找服务
            while($findService==1) {
               $error="";
               try{
                  $this->service = $this->client->getRPCService("Services\\Demo\\HiService",$this->ipAndPort[0],$this->ipAndPort[1]);
                } catch (\Exception $e) {
                    $error= $e->getMessage();
                }catch (\Error $e) {
                     $error = $e;
                 }
                if(!empty($error)){
                     $ip = $this->ipAndPort[0].":".$this->ipAndPort[1];
                     $disableIps[$ip]=$ip ;
                     $error.=" 来自 ".$ip;
                   //  echo $error."</br>";
                     $allError[]=$error;
                     $this->ipAndPort = $this->randIp($disableIps);
               }
               $tryCount--;
               if(count($disableIps)==count($this->serverips)){
                    $findService=0;
               }
               if($tryCount<1){
                   $findService=0;
               }
            }

            if($this->service){
                $returnMsg= json_decode($this->service->say(json_encode($params)),true);
            }else{
                //$returnMsg = $allError;
                 throw new \Exception(serialize ($allError), 1);
            }
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

