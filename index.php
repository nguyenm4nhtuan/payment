<?php
/**
 * Created by PhpStorm.
 * User: MrTuan
 * Date: 6/30/2017
 * Time: 10:25 PM
 */

require_once 'vendor/autoload.php';

echo "<pre>";

$customer = new \Entity\Customer('c1');

$customer->getAccounts();
echo "\n\n";
//die;
$customer->setDefaultAccount(2);
echo "\n\n";

$customer->setDailyTopUpLimit(100);
echo "\n\n";

$customer->topUp(2, 113);
echo "\n\n";

$customer->topUp(2, 50);
echo "\n\n";

$customer->getAccounts();
echo "\n\n";

$customer->transfer(2, 1, 60);
echo "\n\n";

$customer->addAccount('EUR');
echo "\n\n";

$customer->transfer(2, 3, 40);
echo "\n\n";

$customer->findAccount(3);
echo "\n\n";

$customer->getAccounts();
echo "\n\n";

//$account = \Entity\Account::findBy('e_wallet_id', 1);


//var_dump($account);
//var_dump(\Entity\Account::all());
//$customer->freezeAccount(2);
//print_r($customer->getAccounts());