<?php

/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 7/1/2017
 * Time: 9:40 PM
 */

namespace Entity;

use Common\Base;

class Account extends Base
{
    const TYPE = [
        'active' => 1,
        'freeze' => 2
    ];

    public $id;

    public $name;

    public $e_wallet_id;

    public $number;

    public $is_virtual;

    public $is_default;

    public $status;

    public $currency;

    public $amounts;

    public function __construct($eWalletId, $currency,$name = null)
    {
        parent::__construct();
        $this->_init($eWalletId, $currency,$name);
    }

    private function _init($eWalletId, $currency,$name)
    {
        if(!Currency::isCurrency($currency)) {
            throw new \Exception('Currency invalid');
        }

        $this->e_wallet_id = $eWalletId;
        $this->currency = $currency;
        $this->name = $name ?: ucfirst($this->currency) . '-Account-' . $this->id;
        $this->is_virtual = $this->currency === 'credits';
        $this->number = uniqid();
        $this->status = self::TYPE['active'];
        $this->amounts = 0;
        $this->is_default = false;
    }

    public function getAmounts()
    {
        return $this->amounts . ' ' . $this->currency ;
    }

    public function canFreeze()
    {
        return !$this->is_virtual && $this->status === self::TYPE['active'];
    }

    public function freeze()
    {
        $this->status = self::TYPE['freeze'];
        return $this->save();
    }

    public function canSetDefault()
    {
        return !$this->is_virtual && $this->status === self::TYPE['active'];
    }

    public function setIsDefault()
    {
        $this->is_default = true;
        return $this->save();
    }

    public function addAmount($amount)
    {
        $this->amounts += $amount;
        return $this->save();
    }

    public function subtractAmount($amount)
    {
        if($this->canSubtract($amount)) {
            $this->amounts -= $amount;
            return $this->save();
        }
        return false;
    }

    public function canSubtract($amount)
    {
        return $this->amounts >= $amount && $this->status === self::TYPE['active'];
    }

    public function canWithdraw($amount)
    {
        return $this->is_virtual === false && $this->amounts >= $amount;
    }

    public function withdraw($amount)
    {
        if($this->canWithdraw($amount)) {
            $this->amounts -= $amount;
            return $this->save();
        }
        return false;
    }

    public function getStatus()
    {
        return array_search($this->status, self::TYPE);
    }

    public function getDefault()
    {
        return $this->is_default ? 'true' : 'false';
    }
}