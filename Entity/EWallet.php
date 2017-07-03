<?php
/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 6/30/2017
 * Time: 9:37 PM
 */

namespace Entity;

use Common\AccountInterface;
use Common\PLog;

class EWallet extends Base
{

    public $customer_id;
    public $accounts = array();

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

    public function addAccount(AccountInterface $account)
    {
        $this->accounts[$account->id] = $account;
    }

    public function freezeAccount($accountId)
    {
        if (!$this->existsAccount($accountId)) {
            PLog::error(__METHOD__, 'Account : ' . $accountId . ' is not exists');
            return false;
        }

        $account = $this->accounts[$accountId];
        if (!$account->canSetDefault()) {
            PLog::error(__METHOD__, 'Account : ' . $accountId . ' is virtual account  or Frozen can not set default account');
            return false;
        }

        $account->status = Account::TYPE['freeze'];
        $this->accounts[$accountId] = $account;
    }

    public function setDefaultAccount($accountId)
    {

        if (!$this->existsAccount($accountId)) {
            PLog::error(__METHOD__, 'Account : ' . $accountId . ' is not exists');
            return false;
        }

        $account = $this->accounts[$accountId];
        if (!$account->canSetDefault()) {
            PLog::error(__METHOD__, 'Account : ' . $accountId . ' is virtual account  or Frozen can not set default account');
            return false;
        }

        $account->is_default = true;
        $this->accounts[$accountId] = $account;
    }

    public function existsAccount($accountId)
    {
        return isset($this->accounts[$accountId]);
    }

    public function listAccounts()
    {
        return $this->accounts;
    }

    public function findAccount($accountId)
    {
        return $this->existsAccount($accountId) ? $this->accounts[$accountId] : null;
    }

    public function topUp($accountId, $money)
    {
        if (!$this->existsAccount($accountId)) {
            PLog::error(__METHOD__, 'Account : ' . $accountId . ' not exists, can not top up');
            return false;
        }
        $account = $this->accounts[$accountId];
        $account->addAmount($money);
        $this->accounts[$accountId] = $account;

        PLog::info(__METHOD__, 'Top up to : ' . $accountId . $account->currency . ' success');

        return true;
    }

    public function transfer($fromAccountId, $toAccountId, $money)
    {
        if (!$fromAccount = $this->findAccount($fromAccountId)) {
            PLog::error(__METHOD__, 'Account : ' . $fromAccountId . ' not exists, can not top up');
            return false;
        }

        if (!$toAccount = $this->findAccount($toAccountId)) {
            PLog::error(__METHOD__, 'Account : ' . $toAccountId . ' not exists, can not top up');
            return false;
        }

        try {
            $toAmount = Currency::convert($money, $fromAccount->currency, $toAccount->currency);

            $fromAccount->subtractAmount($money);
            $toAccount->addAmount($toAmount);

            PLog::info(__METHOD__, "Transfer {$money} {$fromAccount->currency} from {$fromAccountId} to {$toAccountId} success");
            return true;
        } catch (\Exception $e) {
            $fromAccount->addAmount($money);
            $toAccount->subtractAmount($toAmount);

            PLog::error(__METHOD__, 'Account : ' . $toAccountId . ' not exists, can not top up');
            return false;
        }


    }
}