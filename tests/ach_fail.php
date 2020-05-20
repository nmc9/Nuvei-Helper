<?php

require('setupAchTests.php');



// $paymentParams['ORDERID'] = $orderid;
//* The amount as a two digit decimal */
$paymentParams['PAYMENTTYPE'] = "check";

//* The amount as a two digit decimal */
$paymentParams['AMOUNT'] = 13.03;
$paymentParams['CURRENCY'] = "USD";
// $paymentParams['CARDNUMBER'] = "4111111111111111";
// $paymentParams['CARDHOLDERNAME'] = "Nick Caruso";


$paymentParams['ACCOUNT_TYPE'] = "SAVINGS";
$paymentParams['ACCOUNT_NUMBER'] = "011401534s";
$paymentParams['ROUTING_NUMBER'] = "011401533";
$paymentParams['ACCOUNT_NAME'] = "John Doe";
// $paymentParams['CHECK_NUMBER'] = "1236";
$paymentParams['DL_STATE'] = "NY";
$paymentParams['DL_NUMBER'] = "4353445";

$paymentParams['ADDRESS'] = \Nuvei\Nuvei::buildAddressObject(
	"1st Street",
	null,
	"US",
	null,
	"New York City",
	"12345"
);



$response = $nuvei->sendPayment($paymentParams);
echo 'ach <pre>' , var_dump($response) , '</pre>';
echo 'ach <pre>' , var_dump($response->get_datetime()) , '</pre>';
