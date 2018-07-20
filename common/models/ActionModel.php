<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/19
 * Time: 10:07
 */

namespace common\models;


use yii\base\Model;

class ActionModel  extends  Model
{

    private  $_rfMethod;

    /**
     * 接口方法名
     *
     */
    private  $_name;
    /**
     *接口名称
     */
    private  $_title;

    /**
     * 请求方式($_POST,$_GET)
     */
    private  $_method;

    /**
     * 请求接口方式
     */
    private  $_apitype;

    /**
     * 接口参数
     */
    private  $_params;

    /**
     *默认接口参数
     */
    private  $_paramsDefaultValues = [];

    /**
     * 路由
     */
    private  $_route;

    /**
     * 接口作者
     */
    private  $_author;

    /**
     * 简介
     */
    private  $_uses;

    /**
     * 数据库中保存的扩展内容
     */
    public   $data;

    //ReflectionMethod 类型
    public  function __construct(\ReflectionMethod $method){

        $this->_rfMethod = $method;

        parent::__construct([]);
    }

    public  function  init(){

        $this->_name = $this->_rfMethod->name;

        $params      = $this->_rfMethod->getParameters();
        //$param参数 ReflectionParameter类型
        foreach ($params as $param){
            if($param->isDefaultValueAvailable()){
                $this->_paramsDefaultValues[$param->getName()] = $param->getDefaultValue();
            }
        }
        //获取注释方法的注释
        $comment = $this->_rfMethod->getDocComment();
        if(preg_match_all('/@param\s*(.*)\n/',$comment,$matches) && !empty($matches[1])){
            foreach ($matches[1] as $match){
                $info  = preg_split('/[\s]+/',$match,3);
                $param =[
                        'type'=>isset($info[0])?$info[0]:'',
                        'name'=>isset($info[1])?$info[1]:'',
                        'desc'=>isset($info[2])?$info[2]:'',
                ];

                $this->_params[] = $param;

            }
        }

        //只有函数名称
        if(preg_match('/@name\s*(.*)\n/',$comment,$matches) && !empty($matches[1])){
            $this->_title = trim($matches[1]);
        }else{
            $this->_title = $this->_rfMethod->name;
        }

        if(preg_match('/@method\s*(.*)\n/',$comment,$matches) && !empty($matches[1])){
            $this->_method    = trim($matches[1]);
        }else{
              $this->_method  = 'GET';
        }

        if(preg_match('/@author\s*(.*)\n/',$comment,$matches) && !empty($matches[1])){

            $this->_author = trim($matches[1]);
        }else{
            $this->_author = '';
        }

        if(preg_match('/@uses\s*(.*)\n/',$comment,$matches) && !empty($matches[1])){
            $this->_uses = trim($matches[1]);
        }else{
            $this->_uses = '';
        }

        if(preg_match('/@apitype\s*(.*)\n/',$comment,$matches) && !empty($matches[1])){

            $this->_apitype = trim($matches[1]);
        }else{
            $this->_apitype = '';
        }

        $ms = explode("\\",$this->_rfMethod->class);

        $className    = $ms[count($ms)-1];
        //该方法类似preg_replace,相当于重写replace方法
        $controllerId = trim(preg_replace_callback('/([A-z])/',function ($matches){
                   return '-'.strtolower($matches[0]);
        },substr($className,0,strlen($className) -10 )),'-');

        if('controllers' != $ms[count($ms) -2]){
            $className1    = $ms[count($ms)-2];
            $controllerId1 =  trim(preg_replace_callback('/([A-z])/',function ($matches){

                return '-'.strtolower($matches[0]);
            },$className1),'-');

            $controllerId = $controllerId1.'/'.$controllerId;
        }

        $actionId  = trim(preg_replace_callback('/[A-Z]/',function ($matches){
            return '-'.strtolower($matches[0]);
        },substr($this->_name,6)),'-');

        $this->_route = $controllerId.'/'.$actionId;

    }

   public  function getParamDefaultValue($paramName){

        return isset($this->_paramsDefaultValues[$paramName])?$this->_paramsDefaultValues[$paramName]:'';
   }

   public  function getName(){

        return  $this->_name;
   }


   public  function getTitle(){

        return  $this->_title;
   }

   public  function getMethod(){

        return strtoupper($this->_method);
   }

   public  function getParams(){

        return $this->_params;
   }

   public  function getParamsDefaultValues(){

        return $this->_paramsDefaultValues;
   }

   public  function getRoute(){

        return $this->_route;
   }

   public  function getAuthor(){

        return $this->_author;
   }

   public  function getApiType(){

        return  $this->_apitype;
   }

   public  function getUses(){

        return $this->_uses;
   }


}