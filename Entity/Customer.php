<?php
/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 6/30/2017
 * Time: 9:31 PM
 */

namespace Entity;

use Common\Base;
use Common\PLog;

class Customer extends Base
{
    public $defaultCurrency = 'USD';

    public $limitTopUp;

    public $currencyOfLimitTopUp;

    public $limitWithdrawal;

    public $currencyLimitWithdrawal;

    public function __construct($id)
    {
        $this->_init($id);
    }

    private function _init($id)
    {
        try {
            if (self::find($id)) {
                throw new \Exception("Customer : {$id} is exists in system, please choose other id");
            }

            $this->id = $id;
            $this->currencyLimitWithdrawal = $this->defaultCurrency;
            $this->currencyOfLimitTopUp = $this->defaultCurrency;
            $this->save();
            $eWallet = new EWallet($this->id);
            $eWallet->save();
            $eWallet->initAccounts();

            $msg = "Create account {$this->id} success ";
            PLog::info(__METHOD__, $msg);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            PLog::error(__METHOD__, $msg);
        }
        print_r($msg);
    }

    public function setDailyTopUpLimit($limit)
    {
        if (is_numeric($limit)) {
            $msg = 'Set daily top up limit is : ' . $limit . ' USD';
            $this->limitTopUp = $limit;
            $this->save();
            PLog::info(__METHOD__, $msg);
        } else {
            $msg = $limit . ' is not number ';
            PLog::error(__METHOD__, $msg);
        }
        print_r($msg);
    }

    public function setDailyWithdrawalLimit($limit)
    {
        if (is_numeric($limit)) {
            $msg = 'Set daily withdrawal limit is : ' . $limit . ' USD';
            $this->limitWithdrawal = $limit;
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
        $html = [];
        foreach ($accounts as $account) {
            $item = $this->showDetailAccount($account);
            $html[] = $item;
        }
        PLog::info(__METHOD__, $html);
        foreach ($html as $i) {
            print_r(PHP_EOL);
            print_r($i);
        }
    }

    public function setDefaultAccount($accountId)
    {
        $account = Account::find($accountId);
        if (!$account) {
            $msg = 'Account : ' . $accountId . ' is not exists';
            PLog::error(__METHOD__, $msg);
            print_r($msg);
            return false;
        }

        if (!$account->canSetDefault()) {
            $msg = 'Account : ' . $account->name . ' is virtual account  or Frozen can not set default account';
            PLog::error(__METHOD__, $msg);
            print_r($msg);
            return false;
        }

        if ($account->setIsDefault()) {
            $msg = "Set account : {$account->name} is default success";
            PLog::info(__METHOD__, $msg);
        } else {
            $msg = "Set account : {$account->name} is default error";
            PLog::error(__METHOD__, $msg);
        }
        print_r($msg);
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
            $msg = "Frozen {$account->name}  success";
            PLog::info(__METHOD__, $msg);
        } else {
            $msg = "Freeze {$account->name} not success, Please try again";
            PLog::error(__METHOD__, $msg);
        }
        print_r($msg);
    }

    public function addAccount($currency)
    {
        $eWallet = EWallet::findBy('customer_id', $this->id)[0];
        $account = new Account($eWallet->id, $currency);

        if ($account->save()) {
            $msg = "Add {$account->name} success : " . $this->showDetailAccount($account);
            PLog::info(__METHOD__, $msg);
        } else {
            $msg = "Add {$account->name} error, Please try again";
            PLog::error(__METHOD__, $msg);
        }
        print_r($msg);
    }

    public function canTopUp($money, $currency)
    {
        if (!is_numeric($money)) {
            return;
        }
        $money = Currency::convert($money, $currency, 'USD');
        return $this->limitTopUp === null || $this->limitTopUp >= $money;
    }

    public function topUp($accountId, $money)
    {
        $account = Account::find($accountId);
        if (!$account) {
            $msg = 'Account : ' . $accountId . ' is not exists';
            print_r($msg);
            PLog::error(__METHOD__, $msg);
            return;
        }

        if (!$this->canTopUp($money, $account->currency)) {
            $msg = "Amount invalid or Amount top up bigger your limit top up {$this->limitTopUp} USD, money top up : {$money} {$account->currency}";
            print_r($msg);
            PLog::error(__METHOD__, $msg);
            return;
        }

        if ($account->addAmount($money)) {
            $msg = "You top up  {$money} {$account->currency} for {$account->name} success";
            PLog::error(__METHOD__, $msg);
        } else {
            $msg = "You top up  {$money} {$account->currency} for {$account->name} not success , please try again ";
            PLog::error(__METHOD__, $msg);
        }
        print_r($msg);
    }

    public function canTransfer($account, $money)
    {
        if (!is_numeric($money)) {
            return;
        }
        $money = Currency::convert($money, $account->currency, 'USD');
        return $account->amounts >= $money;
    }

    public function transfer($fromAccountId, $toAccountId, $money)
    {
        if (!is_numeric($money)) {
            $msg = "Amount invalid, please check again : {$money}";
            print_r($msg);
            PLog::error(__METHOD__, $msg);
            return;
        }

        if (!$fromAccount = Account::find($fromAccountId)) {
            $msg = 'Account : ' . $fromAccountId . ' not exists, can not top up';
            print_r($msg);
            PLog::error(__METHOD__, $msg);
            return;
        }

        if (!$toAccount = Account::find($toAccountId)) {
            $msg = 'Account : ' . $toAccountId . ' not exists, can not top up';
            print_r($msg);
            PLog::error(__METHOD__, $msg);
            return;
        }

        if (!$fromAccount->canSubtract($money)) {
            $msg = "Account {$fromAccount->name} not enough money or Account frozen, money transfer : {$money} {$fromAccount->currency}";
            print_r($msg);
            PLog::error(__METHOD__, $msg);
            return;
        }

        try {
            $toAmount = Currency::convert($money, $fromAccount->currency, $toAccount->currency);
            //begin transaction

            $fromAccount->subtractAmount($money);
            $toAccount->addAmount($toAmount);

            //commit transaction

            $msg = "You transfer  {$money} {$fromAccount->currency}  from {$fromAccount->name} to {$toAccount->name} success, {$toAccount->name} has {$toAccount->amounts} {$toAccount->currency}";
            PLog::info(__METHOD__, $msg);
        } catch (\Exception $e) {
            //rollback transaction
            $msg = "You transfer  {$money} {$fromAccount->currency}  from {$fromAccount->name} to {$toAccount->name} not success, please try again ";
            PLog::error(__METHOD__, $msg);
        }
        print_r($msg);
    }

    public function canWithdraw($account, $money)
    {
        if (!is_numeric($money)) {
            return;
        }

        $money = Currency::convert($money, $account->currency, 'USD');

        return $this->limitWithdrawal === null || $this->limitWithdrawal >= $money;
    }

    public function withdraw($accountId, $money)
    {
        $account = Account::find($accountId);
        if (!$account) {
            $msg = 'Account : ' . $accountId . ' is not exists';
            print_r($msg);
            PLog::error(__METHOD__, $msg);
            return;
        }

        if (!$this->canWithdraw($account, $money)) {
            $msg = "Amount withdraw invalid or bigger your limit withdraw ({$this->limitWithdrawal} USD), withdraw: {$money} {$account->currency}";
            print_r($msg);
            PLog::error(__METHOD__, $msg);
            return;
        }

        if(!$account->canWithdraw($money)) {
            $msg = "Account {$account->name} is virtual or not enough money, please check again ";
            print_r($msg);
            PLog::error(__METHOD__, $msg);
            return;
        }

        if ($account->withdraw($money)) {
            $msg = "You withdraw {$money} {$account->currency} from {$account->name} success";
            PLog::info(__METHOD__, $msg);
        } else {
            $msg = "You withdraw {$money} {$account->currency} from {$account->name} not success , please try again ";
            PLog::error(__METHOD__, $msg);
        }
        print_r($msg);
    }

    public function findAccount($accountId)
    {
        $account = Account::find($accountId);
        $html = '';
        if ($account) {
            $html .= $this->showDetailAccount($account);
            PLog::info(__METHOD__, $html);
        } else {
            $html .= "Account {$accountId} not exists";
            PLog::error(__METHOD__, $html);
        }
        print_r($html);
    }

    public function showDetailAccount($account)
    {
        $html = 'Account id: ' . $account->id;
        $html .= ' -- ';
        $html .= 'Account name: ' . $account->name;
        $html .= ' -- ';
        $html .= 'Balance : ' . $account->getAmounts();
        $html .= ' -- ';
        $html .= 'Status : ' . $account->getStatus();
        $html .= ' -- ';
        $html .= 'Default : ' . $account->getDefault();
        return $html;
    }

    public function showDetail()
    {
        $html = 'Customer id: ' . $this->id;
        $html .= ' -- ';
        $html .= 'Limit Top Up : ' . $this->limitTopUp . ' ' . $this->currencyOfLimitTopUp;
        $html .= ' -- ';
        $html .= 'Limit Withdraw : ' . $this->limitWithdrawal . ' ' . $this->currencyLimitWithdrawal;

        PLog::info(__METHOD__, $html);
        print_r($html);
    }
}