<?php

/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 7/1/2017
 * Time: 9:40 PM
 */

namespace Entity;

use Common\AccountInterface;

class Account extends Base implements AccountInterface
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
        $this->e_wallet_id = $eWalletId;
        $this->name = $name;
        $this->currency = $currency;
        $this->_init();

    }

    private function _init()
    {
        if(!Currency::isCurrency($this->currency)) {
            throw new \Exception('Currency invalid');
        }

        $this->name = $this->name ?: ucfirst($this->currency) . '-Account';
        $this->is_virtual = $this->currency === 'credits';
        $this->number = uniqid();
        $this->status = self::TYPE['active'];
    }

    public function getAmounts()
    {
        return ($this->amounts ?: 0) . ' ' . $this->currency ;
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
            return true;
        } else {
            return false;
        }
    }

    public function canSubtract($amount)
    {
        return $this->amounts >= $amount && $this->status === self::TYPE['active'];
    }

    public function toUp()
    {
        // TODO: Implement toUp() method.
    }

    public function withdraw()
    {
        // TODO: Implement withdraw() method.
    }

    public function transfer()
    {
        // TODO: Implement transfer() method.
    }
}