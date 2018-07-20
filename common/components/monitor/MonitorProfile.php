<?php
namespace common\components\monitor;
use common\entity\AssetSqlMonitorEntity;

/**
 *
 * author: paulversion
 * Date: 2018/7/20
 * Time: 20:05
 */

class MonitorProfile
{
    const QUERY   = 'QUERY';
    const DELETE  = 'DELETE';
    const INSERT  = 'INSERT';
    const UPDATE  =  'UPDATE';

    public static $exclude = ['select 1',null];


    public  static  function sqlQueryMonitorProfile($traceSql){

        $sqlMonitorEntity = new AssetSqlMonitorEntity();
        $sqlMonitorEntity->setBeginTime(microtime(true));
        $sqlMonitorEntity->setType(self::QUERY);
        $sqlMonitorEntity->setSql($traceSql);

        return $sqlMonitorEntity;

    }

    public  static  function sqlExMonitorProfile($traceSql,$exType){

        $sqlMonitorEntity = new AssetSqlMonitorEntity();
        $sqlMonitorEntity->setBeginTime(microtime(true));
        $sqlMonitorEntity->setType($exType);
        $sqlMonitorEntity->setSql($traceSql);

        return  $sqlMonitorEntity;

    }

    /**
     * @param AssetSqlMonitorEntity $sqlMonitorEntity
     */
    public  static  function upload($sqlMonitorEntity){

        if(in_array($sqlMonitorEntity->getSql(),self::$exclude) || (is_array($sqlMonitorEntity)
                && isset($sqlMonitorEntity['sql']) && in_array($sqlMonitorEntity['sql'],self::$exclude))){

            return true;

        }
        $isPush = false;
        if($sqlMonitorEntity instanceof AssetSqlMonitorEntity){
            $monitor = $sqlMonitorEntity->toJson();
            $isPush  = true;
        }elseif (is_array($sqlMonitorEntity)){

            $monitor = json_encode($sqlMonitorEntity);
            $isPush  = true;
        }


    }

    public  static  function push($monitorTrace){

        if(YII_ENV != YII_ENV_PROD){
            return true;
        }
        if(is_array($monitorTrace)){
            $data = json_encode($monitorTrace,JSON_UNESCAPED_UNICODE);
        }else{
            $data = $monitorTrace;
        }
        

    }

}