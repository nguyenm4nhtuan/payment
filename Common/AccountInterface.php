<?php

/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 6/30/2017
 * Time: 10:01 PM
 */

namespace Common;

interface AccountInterface
{
    public function toUp();

    public function withdraw();

    public function transfer();
}