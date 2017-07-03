<?php
/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 6/30/2017
 * Time: 9:37 PM
 */

namespace Entity;

use Common\Base;
use Common\PLog;

class EWallet extends Base
{

    public $customer_id;

    protected $_initCurrencies = ['credits', 'USD'];

    public function __construct($customerId)
    {
        parent::__construct();
        $this->customer_id = $customerId;
    }

    public function initAccounts()
    {
        try {
            foreach ($this->_initCurrencies as $id => $currency) {
                $account = new Account($this->id, $currency);
                $account->save();
            }
        } catch (\Exception $e) {
            PLog::error(__METHOD__, 'Can not initialization account ');
        }
    }
}