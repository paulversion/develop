<?php
namespace  common\entity;
/**
 *
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/20
 * Time: 20:10
 */
class AssetSqlMonitorEntity
{

    private $sql;
    private $type;
    private $beginTime;
    private $endTime;
    private $errorNo;
    private $errorMsg;
    private $traces = [];


    public function  getTraces(){

        return  $this->traces;
    }

    public  function setTraces($traces){
        $count = 0;
        $level = 5;
        array_pop($traces);// // remove the last trace since it would be the entry script, not very useful
        foreach ($traces as $trace){
            if(isset($trace['file'],$trace['line']) && strpos($trace['file'],YII2_PATH)!== 0){
              unset($trace['object'],$trace['args']);
              $ts[] = $trace;
              if(++$count > $level){
                  break ;
              }
            }
        }

        $this->traces =$ts;
    }


    public  function getSql(){

        return $this->sql;
    }

    public  function  setSql($sql){

        $this->sql = $sql;
    }

   public  function  getType(){
        return $this->type;
   }

   public  function setType($type){
        $this->type = $type;
   }

   public  function  getBeginTime(){

        return  $this->beginTime;
   }

   public  function setBeginTime($beginTime){
        $this->beginTime = $beginTime;
   }

   public  function getEndTime(){

        return $this->endTime;
   }

   public  function setEndTime($endTime){
        $this->endTime = $endTime;
   }

   public  function getErrorNo(){

        return $this->errorNo;
   }

   public  function  setErrorNo($errorNo){

        $this->errorNo = $errorNo;
   }

   public  function getErrorMsg(){

        return  $this->errorMsg;
   }

   public  function setErrorMsg($errorMsg){

        $this->errorMsg = $errorMsg;
   }


   public  function toJson(){
        $spanTime = ($this->getEndTime() - $this->getBeginTime());

        return json_encode([
                             'spanTime'   => $spanTime,
                             'sqlType'    => $this->getType(),
                             'sql'        => $this->getSql(),
                             'beginTime'  => $this->getBeginTime(),
                             'errorMsg'   => $this->getErrorMsg(),
                             'errorNo'    => $this->getErrorNo(),
                             'traces'     => json_encode($this->getTraces()),

                        ]);

   }



}