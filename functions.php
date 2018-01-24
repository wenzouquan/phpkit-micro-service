<?php
namespace phpkit\microservice;

function getArg($name="",){
   $arg = $GLOBALS['microserviceArguments'];
   if($name){
   	 return $arg[$name];
   }else{
   	return $arg;
   }
}

function setArg($data=[]){
	 $GLOBALS['microserviceArguments']=$data;
}
