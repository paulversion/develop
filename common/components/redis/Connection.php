<?php
/**
 * Created by PhpStorm.
 * User: paulv
 * Date: 2018/3/26
 * Time: 22:31
 */
namespace common\components\redis;

class Connection extends \yii\redis\Connection
{

     public  $redis    =  null;
     public  $timeout  = 20;
     public  $prefix   = '';

    public  function  init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }

    public  function  open()
    {
          if(!($this->redis instanceof \Redis)){
              $this->redis = new \Redis();
          }

          $this->redis->connect($this->hostname,$this->port,$this->timeout);

          if($this->prefix){
              $this->redis->setOption(\Redis::OPT_PREFIX,$this->prefix);
          }
    }

    public  function  executeCommand($name, $params = [])
    {
            $this->open();
            return call_user_func_array([$this->redis,$name],$params);#这里调用了call_user_func_array


    }

    public  function __call($name, $params)
    {
        $redisCommand = strtoupper($name);
        if (in_array($redisCommand, $this->redisCommands)) {
            return $this->executeCommand($redisCommand, $params);
        } else {
            return parent::__call($name, $params);
        }
    }


}