<?php
/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 6/30/2017
 * Time: 9:31 PM
 */

namespace Entity;

use Common\PLog;

class Customer extends Base
{
    public $id;

    public $limitTopUp;

    public $currencyOfLimitTopUp = 'USD';

    public $limitWithdrawal;

    public $currencyLimitWithdrawal = 'USD';

    public function __construct($id)
    {
        $this->id = $id;
        $this->_init();
    }

    private function _init()
    {
        if (!$this->_isValidId()) {
            throw new \Exception('Id is exists in system !');
        }
        $this->save();
        $eWallet = new EWallet($this->id);
        $eWallet->save();
        $eWallet->initAccounts();
    }

    private function _isValidId()
    {
        return !isset($GLOBALS['customers'][$this->id]);
    }

    public function setDailyTopUpLimit($limit)
    {
        if (is_numeric($limit)) {
            print_r('Set daily top up limit is : ' . $limit . 'USD');
            $this->limitTopUp = $limit;
            PLog::info('setDailyTopUpLimit', 'Set daily top up limit is : ' . $limit . 'USD');
        } else {
            print_r($limit . ' is not number ');
            PLog::error('setDailyTopUpLimit', $limit . ' is not number ');
        }
    }

    public function setDailyWithdrawalLimit($limit)
    {
        if (is_numeric($limit)) {
            $msg = 'Set daily withdrawal limit is : ' . $limit . 'USD';
            $this->limitTopUp = $limit;
            $this->save();
            PLog::info(__METHOD__, $msg);
        } else {
            $msg = $limit . ' is not number ';
            PLog::error(__METHOD__, $msg);
        }
        print_r($msg);
    }

    public function getAccounts()
    {
        $eWallet = EWallet::findBy('customer_id', $this->id)[0];
        $accounts = Account::findBy('e_wallet_id', $eWallet->id);
        $html = '';
        foreach ($accounts as $account) {
            $html .= "\n";
            $html .= 'Account id: ' . $account->id;
            $html .= ' -- ';
            $html .= 'Account name: ' . $account->name;
            $html .= ' -- ';
            $html .= 'Balance : ' . $account->getAmounts();
        }
        PLog::info(__METHOD__, $html);
        print_r($html);
    }

    public function setDefaultAccount($accountId)
    {
        $account = Account::find($accountId);
        if (!$account) {
            PLog::error(__METHOD__, 'Account : ' . $account->name . ' is not exists');
            return false;
        }

        if (!$account->canSetDefault()) {
            PLog::error(__METHOD__, 'Account : ' . $account->name . ' is virtual account  or Frozen can not set default account');
            return false;
        }

        if ($account->setIsDefault()) {
            PLog::info(__METHOD__, "Set account : {$account->name} is default success");
            print_r("Set account : {$account->name} is default success");
        } else {
            PLog::error(__METHOD__, "Set account : {$account->name} is default error");
            print_r("Set account : {$account->name} is default error, Please try again");
        }
    }

    public function freezeAccount($accountId)
    {
        $account = Account::find($accountId);
        if (!$account) {
            PLog::error(__METHOD__, 'Account : ' . $accountId . ' is not exists');
            return;
        }

        if (!$account->canFreeze()) {
            PLog::error(__METHOD__, 'Account : ' . $accountId . ' is virtual account  or Frozen can not set default account');
            return;
        }

        if ($account->freeze()) {
            PLog::info(__METHOD__, "Frozen {$account->name}  success");
            print_r("You Frozen {$account->name}  success");
        } else {
            PLog::error(__METHOD__, "You Frozen {$account->name}  error");
            print_r("Freeze {$account->name} not success, Please try again");
        }
    }

    public function canTopUp($money, $currency)
    {
        $money = Currency::convert($money, $currency, 'USD');
        return $this->limitTopUp === null || $this->limitTopUp >= $money;
    }

    public function topUp($accountId, $money)
    {
        $account = Account::find($accountId);
        if (!$account) {
            print_r('Account : ' . $accountId . ' is not exists');
            PLog::error(__METHOD__, 'Account : ' . $accountId . ' is not exists');
            return;
        }
        if (!$this->canTopUp($money, $account->currency)) {
            print_r("Amount top up bigger your limit top up");
            PLog::error(__METHOD__, 'Amount top up bigger your limit top up');
            return;
        }

        if ($account->addAmount($money)) {
            print_r("You top up  {$money} {$account->currency} success");
        } else {
            print_r("Your trade not success , please try again ");
        }
    }

    public function canTransfer($account, $money)
    {
        $money = Currency::convert($money, $account->currency, 'USD');
        return $account->amounts >= $money && ($this->limitWithdrawal === null || $this->limitWithdrawal >= $money);
    }

    public function transfer($fromAccountId, $toAccountId, $money)
    {
        if (!$fromAccount = Account::find($fromAccountId)) {
            PLog::error(__METHOD__, 'Account : ' . $fromAccount->name . ' not exists, can not top up');
            return;
        }

        if (!$toAccount = Account::find($toAccountId)) {
            PLog::error(__METHOD__, 'Account : ' . $toAccount->name . ' not exists, can not top up');
            return;
        }

        if (!$this->canTransfer($fromAccount, $money)) {
            $msg = "Amount transfe bigger your limit transfer or Account not enough money";
            print_r($msg);
            PLog::error(__METHOD__, $msg);
            return;
        }

        try {
            $toAmount = Currency::convert($money, $fromAccount->currency, $toAccount->currency);

            $fromAccount->subtractAmount($money);
            $toAccount->addAmount($toAmount);

            $msg = "You transfer  {$money} {$fromAccount->currency}  from {$fromAccount->name} to {$toAccount->name} success";
            print_r($msg);
            PLog::info(__METHOD__, $msg);
        } catch (\Exception $e) {
            $msg = "You transfer  {$money} {$fromAccount->currency}  from {$fromAccount->name} to {$toAccount->name} not success, please try again ";
            print_r($msg);
            PLog::error(__METHOD__, $msg);
        }
    }

    public function addAccount($currency)
    {
        $eWallet = EWallet::findBy('customer_id', $this->id)[0];
        $account = new Account($eWallet->id, $currency);

        if ($account->save()) {
            $msg = "Add {$account->name} success";
            PLog::info(__METHOD__, $msg);
        } else {
            $msg = "Add {$account->name} error, Please try again";
            PLog::error(__METHOD__, $msg);
        }

        print_r($msg);
    }

    public function findAccount($accountId)
    {
        $account = Account::find($accountId);
        $html = '';
        if($account) {
            $html .= "\n";
            $html .= 'Account id: ' . $account->id;
            $html .= ' -- ';
            $html .= 'Account name: ' . $account->name;
            $html .= ' -- ';
            $html .= 'Balance : ' . $account->getAmounts();
            PLog::info(__METHOD__, $html);
        } else {
            $html .= "Account {$accountId} not exists";
            PLog::error(__METHOD__, $html);
        }
        print_r($html);
    }
}