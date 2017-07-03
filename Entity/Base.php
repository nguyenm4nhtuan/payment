<?php
/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 7/2/2017
 * Time: 10:10 AM
 */

namespace Entity;


use Common\PLog;

class Base
{
    public $id;

    public function __construct()
    {
        $this->id = count(self::all()) + 1;
    }

    public function save()
    {
        try {
            $GLOBALS[static::class][$this->id] = $this;
            return $this;
        } catch (\Exception $e) {
            PLog::error('Save : ' . static::class, $e->getMessage());
            return false;
        }
    }

    public static function find($id)
    {
        return isset($GLOBALS[static::class][$id]) ? $GLOBALS[static::class][$id] : null;
    }

    public static function all()
    {
        return isset($GLOBALS[static::class]) ? $GLOBALS[static::class] : [];
    }

    public static function findBy($key, $value)
    {
        $res = array();
        if(!property_exists(static::class, $key)) {
            return $res;
        }

        foreach (self::all() as $element) {
            if($element->$key == $value) {
                $res[] = $element;
            }
        }
        return $res;
    }
}