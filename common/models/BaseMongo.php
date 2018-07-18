<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/18
 * Time: 19:37
 */

namespace common\models;


use yii\mongodb\ActiveRecord;
use Yii;
class BaseMongo extends ActiveRecord
{
    public static $log;

    public static $_log_begin;
    public static $_detail = [];
    public static $_result = [];
    private static $_document = ['title'=>'','cat_begin_time'=>'','cat_end_time'=>'','begin_time' => '',
                                 'end_time'=>'','err_code'=>'','err_msg'=>'','exception'=>'','trace_details'=>''];

    /**
     * 获取mongo数据库的名称
     * @return \yii\mongodb\Connection
     */
    public  static  function getDb()
    {
        return Yii::$app->get(static::$db_name);
    }

    /**
     * 设置mongo数据库的名称
     * @param $db_name
     */
    public  static  function setDb($db_name){

        static::$db_name = $db_name;
    }
    /**
     * 获取mongo的集合,集合类似于关系数据库中的表
     * @return \yii\mongodb\Collection
     */
    public  static  function getCollectionName()
    {
           return static::$collection_name;
    }

    /**
     * 设置集合的名称
     * @param $collection_name
     */
    public  static function setCollectionName($collection_name){

        static::$collection_name = $collection_name;
    }

    public  function initMongo($action){

        if(!isset($action) || !isset($action->controller) || !isset($action->actionMethod)){
            return false;
        }

        $controller  = & $action->controller;
        //在控制器中$mongoName['方法名称'=>['mongo集合名称','mongo方法名称'],]
        if(isset($controller->mongoName) && isset($controller->mongoName[$action->actionMethod])){
            static::$collection_name = $controller->mongoName[$action->actionMethod][0];
            if(isset($controller->mongoName[$action->actionMthod][1])){
                static::$db_name  = $controller->mongoName[$action->actionMthod][1];
            }

        }
        $class_name  = get_class($controller);


    }

    public  static  function openLog(){

        self::clearData();
        self::$_log_begin = true;
    }

    public  static function closeLog(){
        self::$_log_begin = false;
    }

    public static  function  canLog(){
        return self::$_log_begin;
    }

    private static  function clearData(){

        self::$_document = ['title'=>'','begin_time'=>0,'end_time'=>0,'error_code'=>0,'err_msg'=>'','exception'=>'','trace_detail'=>''];
        self::$_detail   = [];
        self::$_result   = [];

    }

    public  static  function getMethodName($class_name,$action_name){

        $rf       = new \ReflectionClass($class_name);
        $methods   = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method){
           if(strpos($method->name,'action') === false || $method->name == 'actions'){
               continue;
           }
        }


    }




}