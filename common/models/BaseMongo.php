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
    //mongo实例对象
    public static $log;

    public    static  $log_begin;
    protected static  $details = [];
    private   static  $result = [];
    private   static  $log_details =[];
    private   static  $document = ['title'=>'','cat_begin_time'=>'','cat_end_time'=>'','begin_time' => '',
                                    'end_time'=>'','err_code'=>'','err_msg'=>'','exception'=>'','trace_details'=>''];

    public $topic;
    public $log_store;

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
        $title       = $this->getMethodName($class_name,$action->actionMethod);


    }

    public  static  function openLog(){
        self::clearData();
        self::$log_begin = true;
    }

    public  static function closeLog(){
        self::$log_begin = false;
    }

    public static  function  canLog(){
        return self::$log_begin;
    }

    private static  function clearData(){

        self::$document = ['title'=>'','begin_time'=>0,'end_time'=>0,'error_code'=>0,'err_msg'=>'','exception'=>'','trace_detail'=>''];
        self::$details   = [];
        self::$result   = [];

    }

    /***
     * @param $class_name
     * @param $action_name
     * @throws \ReflectionException
     */
    private  function getMethodName($class_name,$action_name){

        $rf       = new \ReflectionClass($class_name);
        $methods   = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method){
           if(strpos($method->name,'action') === false || $method->name == 'actions'){
               continue;
           }
           $actionModel = new ActionModel($method);
           if($action_name == $method->name){
               return  $actionModel->getTitle();
           }

        }

        return '';
    }

    public  function begin($action_name='',$title=''){
        self::openLog();
        $this->addLogField('begin_time',date("Y-m-d H:i:s"));
        $this->addLogField('cat_begin_time',microtime());
        $this->addLogField('action',$action_name);
        $this->addLogField('title',$title);
        if(isset($_REQUEST)){
            self::$log->addLogDetail(self::$document['title'].'-开始-请求参数',$_REQUEST);
        }
        if(isset($_SERVER['REMOTE_ADDR'])){
            $this->addLogField('server_ip',$_SERVER['REMOTE_ADDR']);
        }

        register_shutdown_function(function (){
            $this->end();
        });
    }

    public  function  end($result){

        if(!self::canLog() || empty(self::$document) || empty(self::$log)){
            return false;
        }
        $this->addLogField('end_time',date('Y-m-d H:i:s'));
        $this->addLogField('cat_end_time',microtime());
        if(!empty($result)){
            self::$result = $result;
        }
        self::$log->addLogDetail(self::$document['title'].'-结束-返回数据',self::$result);
        self::$document['details'] = self::$details;
        $this->addLogField('response',$result);
        if($this->log_store && $this->topic){
             $monitorTrack = [];
            if(Yii::$app instanceof \yii\web\Application){
                $monitorTrack['log_store'] = $this->log_store;
                $monitorTrack['topic']     = $this->topic;
                $monitorTrack['upload_data'] = self::$document;
            }
            if(Yii::$app instanceof \Yii\web\Application && count(self::$log_details) > 4){
                $monitorTrack['log_store'] = $this->log_store;
                $monitorTrack['topic'] = $this->topic;
                $monitorTrack['upload_data'] = array_merge(self::$document,['console_log' => self::$log_details]);
            }
        }
        try{

            $collection = self::getCollection();
            $rs         = $collection->insert(self::$document);

        }catch (\Exception $exception){

            Yii::info(var_export(self::$document),true);
        }

        self::closeLog();
        self::clearData();




    }

    /**
     * 向mongo的document添加属性
     * @param [string] $field_name 属性名
     * @param [string|array] $value 属性具体内容
     *
     */
    public  function addLogField($field_name,$value){
        if(!self::canLog()){
            return false;
        }
        self::$document[$field_name] = $value;

    }

    /**
     * 给记录叠加属性
     * @param [string] $title
     * @param [string|array] $value
     *
     */
    public function addLogFieldDetails($title,$value){

        if(!self::canLog() || empty($title)){

            return false;
        }
        $log_detail = [];

        if(is_array($value)){
            $log_detail = array_merge($log_detail,$value);
        }else{
            $log_detail['result'] = $value;
        }
        array_push(self::$log_details,$log_detail);

    }

    /**
     * 将记录日志的字段添加到details 数组
     * @param $title 日志标题
     * @param string $log_data 日志内容
     *
     */
    public  function addLogDetail($title,$log_data=''){

        if(!self::canLog() || empty($title)){
            return false;
        }
        self::is_error($log_data);
        $log_detail = [];
        if(is_array($log_data)){
            $log_detail = array_merge($log_detail,$log_data);
        }else{
            $log_detail['result'] = $log_data;
        }

       array_push(self::$details,$log_detail);

    }


    /**
     * 日志是否出错
     * @param $log_data
     * @return bool
     */
    private static function is_error($log_data){

        if(is_string($log_data)){
            return false;
        }

        if(!empty($log_data) && (isset($log_data['err_code']) || isset($log_data['err_msg']))){
            self::$document['err_code'] = $log_data['err_code'];
            self::$document['err_msg']  = $log_data['err_msg'];
        }

    }

    public static function addException($exception){

        if(!self::canLog()) return false ;


    }








}