<?php
namespace phpkit\microservice;
class StoreArg{
	 public $arguments;
	 public function className($className=""){
	 	$this->className = $className;
	 	return $this;
	 }

	 public function method($method=""){
		$this->method = $method;
	 	return $this;
	 }

	 public function get($key="",$name=""){
	 	$res;
	 	$arguments = $this->arguments[$className][$method];
	 	if($key){
	 		$res = $arguments[$key];
	 	}
	 	if($name){
	 		$res = $arguments[$key][$name];
	 	}
	 	return $res;
	 }


	 public function set($data){
	 		$this->arguments[$data['className']][$data['method']]=$data['arguments'];
	 }



}

$GLOBALS['storeArg2018028bywinn'] = new StoreArg();

function args(){
	return $GLOBALS['storeArg2018028bywinn'];
}
