############
In project I use "http://fixer.io/" for get convert rate currency , 
If can not call api convert, system will use Conversion rate in Object Currency

############
I make Example of scenario under :

You can add more scenario test in index.php

Please go to root project and run "php index.php" in command line
(You need install php before run command above, Check installed : php -v )

You can see result in console or in log file at : "root-project/logs/payment.log"

//Create new customer with id = test_customer_12
$test_customer_12 = new \Entity\Customer('test_customer_12');

// Show list account
$test_customer_12->getAccounts();


// Set account USD-Account is default account
$test_customer_12->setDefaultAccount(2);


// Set account Credits-Account is default account
$test_customer_12->setDefaultAccount(1);


// Set account not exists is default account
$test_customer_12->setDefaultAccount(7);


// Set daily limit top up is 100
$test_customer_12->setDailyTopUpLimit(100);


// Top up 113 USD for account USD-Account
$test_customer_12->topUp(2, 113);


// Top up 50 USD for account USD-Account
$test_customer_12->topUp(2, 57);

// Top up 30 credits for account Credits-Account
$test_customer_12->topUp(1, 30);

// Transfer 60 USD form account USD Account to Credits Account
$test_customer_12->transfer(2, 1, 60);


// Add Account with currency is UER
$test_customer_12->addAccount('EUR');


// Transfer 40 USD form account USD Account to EUR Account
$test_customer_12->transfer(2, 3, 40);


//Show detail EUR Account
$test_customer_12->findAccount(3);


// Withdraw 5 USD form USD Account
$test_customer_12->withdraw(2, 5);

// Withdraw 15 credits form Credits Account
$test_customer_12->withdraw(1, 15);
newLine();

// Set daily withdrawal limit is 10
$test_customer_12->setDailyWithdrawalLimit(10);


// Withdraw 20 UER form USD Account
$test_customer_12->withdraw(3, 20);


// Withdraw 5 UER form USD Account
$test_customer_12->withdraw(3, 5);

// Withdraw invalid amount form UER Account
$test_customer_12->withdraw(3, 'text');


// Freeze UER Account
$test_customer_12->freezeAccount(3);


// Show detail Customer
$test_customer_12->showDetail();


// Show detail Accounts
$test_customer_12->getAccounts();

// Add new customer with duplicate id
$test_customer_12_1 = new \Entity\Customer('test_customer_12');

//Show all customers
print_r(\Entity\Customer::all());


