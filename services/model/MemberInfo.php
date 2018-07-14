<?php
/**
 * Created by PhpStorm.
 * User: paulv
 * Date: 2018/3/27
 * Time: 22:07
 */

namespace services\model;


use common\models\User;

class MemberInfo extends CacheBase
{

    public  $modelClass;

    public static $MEMBER;



    public  function attributes()
    {

       return ['name'];

    }


    public  function getData(){

        if(static::$MEMBER == null){
            static::$MEMBER = new Member();
        }
        $a = ['name' => 'paul','age'=>20,'sex'=>'男'];

        \Yii::configure(static::$MEMBER,$a);

    }


    public static  function get(){
        $my = new static;
        $my->getData();
        return static::$MEMBER;
    }


}