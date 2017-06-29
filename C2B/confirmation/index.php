<?php

include "../../Classes/Utils.php";
include "../../Classes/Config.php";

$request = file_get_contents("php://input");

Utils::logThis("INFO", __METHOD__ . "|received request " . print_r($request, true));


$transaction = Utils::processC2BConfirmationRequest($request);

$xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:c2b="http://cps.huawei.com/cpsinterface/c2bpayment">
   <soapenv:Header/>
   <soapenv:Body>
      <c2b:C2BPaymentConfirmationResult>C2B Payment Transaction ' . $transaction . ' result received.</c2b:C2BPaymentConfirmationResult>
   </soapenv:Body>
</soapenv:Envelope>';

Utils::logThis("INFO", "Acknowleding C2B confirmation " . $xml);
echo $xml;

