<?php
/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 6/30/2017
 * Time: 10:25 PM
 */

require_once 'vendor/autoload.php';

function newLine() {
    echo PHP_EOL . PHP_EOL;
}

//Create new customer with id = test_customer_12
$test_customer_12 = new \Entity\Customer('test_customer_12');
newLine();

// Show list account
$test_customer_12->getAccounts();
newLine();

// Set account USD-Account is default account
$test_customer_12->setDefaultAccount(2);
newLine();

// Set account Credits-Account is default account
$test_customer_12->setDefaultAccount(1);
newLine();

// Set account not exists is default account
$test_customer_12->setDefaultAccount(7);
newLine();

// Set daily limit top up is 100
$test_customer_12->setDailyTopUpLimit(100);
newLine();

// Top up 113 USD
$test_customer_12->topUp(2, 113);
newLine();

// Top up 50 USD
$test_customer_12->topUp(2, 57);
newLine();

// Top up 30 credits for account Credits-Account
$test_customer_12->topUp(1, 30);
newLine();

// Transfer 60 USD form account USD Account to Credits Account
$test_customer_12->transfer(2, 1, 60);
newLine();

// Add Account with currency is UER
$test_customer_12->addAccount('EUR');
newLine();

// Transfer 40 USD form account USD Account to EUR Account
$test_customer_12->transfer(2, 3, 40);
newLine();

//Show detail EUR Account
$test_customer_12->findAccount(3);
newLine();

// Withdraw 5 USD form USD Account
$test_customer_12->withdraw(2, 5);
newLine();

// Withdraw 15 credits form Credits Account
$test_customer_12->withdraw(1, 15);
newLine();

// Set daily withdrawal limit is 10
$test_customer_12->setDailyWithdrawalLimit(10);
newLine();

// Withdraw 20 UER form UER Account
$test_customer_12->withdraw(3, 20);
newLine();

// Withdraw 5 UER form UER Account
$test_customer_12->withdraw(3, 5);
newLine();

// Withdraw invalid amount form UER Account
$test_customer_12->withdraw(3, 'text');
newLine();

// Freeze UER Account
$test_customer_12->freezeAccount(3);
newLine();

// Show detail Customer
$test_customer_12->showDetail();
newLine();

// Show detail Accounts
$test_customer_12->getAccounts();
newLine();

$test_customer_12_1 = new \Entity\Customer('test_customer_12');
newLine();

//Show all customers
print_r(\Entity\Customer::all());
