<?php
/*
* import checksum generation utility
* You can get this utility from https://developer.paytm.com/docs/checksum/
*/
require_once("PaytmChecksum.php");
require_once("keys.php");
$data = json_decode(file_get_contents('php://input'), true);
$orderId = $data["orderId"];
$amt = $data["amt"];


$paytmParams = array();


$paytmParams["body"] = array(
    "requestType"   => "Payment",
    "mid"           => $mid,
    "websiteName"   => "WEBSTAGING",
    "orderId"       => $orderId,
    "callbackUrl" => "https://securegw-stage.paytm.in/theia/paytmCallback?ORDER_ID=".$orderId,
    "txnAmount"     => array(
        "value"     => $amt,
        "currency"  => "INR",
    ),
    "userInfo"      => array(
        "custId"    => "CUST_0013",
    ),
);


/*
* Generate checksum by parameters we have in body
* Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
*/
$checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $merchant_Key);

$paytmParams["head"] = array(
    "signature"    => $checksum
);

$post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

/* for Staging */
$url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=".$mid."&orderId=".$orderId;

/* for Production */
// $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=".$mid."&orderId=".$orderId;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); 
$response = curl_exec($ch);
echo $response;
