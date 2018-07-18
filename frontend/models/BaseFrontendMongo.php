<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/7/18
 * Time: 19:36
 */

namespace frontend\models;


use common\models\BaseMongo;

class BaseFrontendMongo extends BaseMongo
{
    public  static $db_name;
    public static $collection_name;

}