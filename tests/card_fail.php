<?php

require('setupTests.php');


// $paymentParams['ORDERID'] = $orderid;
//* The amount as a two digit decimal */
$paymentParams['PAYMENTTYPE'] = "card";

$paymentParams['AMOUNT'] = 1211.05;
$paymentParams['CURRENCY'] = "USD";
$paymentParams['CARDNUMBER'] = "4111111111111111";
$paymentParams['CARDHOLDERNAME'] = "John!! Doe";
//month two digits (09 for september)
$paymentParams['MONTH'] = "12";
//year two digits (16 for 2016)
$paymentParams['YEAR'] = "11";
//CVV 3 or 4 Digits depending on vendor
$paymentParams['CVV'] = "123";

// $paymentParams['CUSTOM_CVV'] = "411";

// $paymentParams['ADDRESS'] = \Nuvei\Nuvei::buildAddressObject(
// 	"1st Street",
// 	null,
// 	"US",
// 	null,
// 	"Willamsport",
// 	"17702",
// 	null
// );
// $address1 = null ,$address2 = null ,$country = null ,$region = null ,$city = null, $postcode = null ,$ip = null ,$phone = null ,$email = null

$response = $nuvei->sendPayment($paymentParams);
echo '<pre>' , var_dump($response) , '</pre>';
