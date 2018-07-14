<?php
/**
 * Created by PhpStorm.
 * User: paulv
 * Date: 2018/3/28
 * Time: 22:34
 */

namespace services\model;


use yii\base\Model;

/**
 * Class Member
 * @package services\model
 * @property  string     $name
 *
 * @property  integer
 */
class Member extends Model
{
    private $_attributes = [];

    public function __set($name, $value)
    {
        $this->_attributes[$name] = $value;
    }


    public  function __get($name)
    {
        //$m = new Zhang();

        if(isset($this->_attributes[$name])||key_exists($name,array_keys($this->_attributes))){

            return $this->_attributes[$name];
        }
    }


}