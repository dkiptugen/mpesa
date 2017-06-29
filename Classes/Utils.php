<?php

/**
 * Description of newPHPClass
 *
 * @author @elijakey
 */
class Utils {
    /* Below denoted different transaction type numbers (important when generating refIDs */

    CONST B2C = 1;
    CONST C2B_TRANSACTION_RECIEVED = 29;
    CONST GLOBAL_TRANSACTION_FAILED = 3;
    CONST C2B_TRANSACTION_SENT_TO_ISO_LISTENER_SUCCESS = 31;
    CONST C2B_TRANSACTION_SENT_TO_ISO_LISTENER_FAILED = 32;

    public static function validatePayload($credentials, $payload, $transactionType = 'B2C') {

        $return = array('STATUS' => false, "DESCRIPTION" => "");
        if (!isset($credentials['username']) or ! isset($credentials['password'])) {
            Utils::logThis("ERROR", "Credentials not provided " . serialize($credentials));
            $return['DESCRIPTION'] = "Credentials not provided";
            return $return;
        }

        if ($credentials['username'] != "admin" or $credentials['password'] != 'admin123') {
            Utils::logThis("ERROR", "Invalid credentials " . serialize($credentials));
            $return['DESCRIPTION'] = "Invalid credentials";
            return $return;
        }

        if (!isset($_SERVER['REMOTE_ADDR']) or ( !in_array($_SERVER['REMOTE_ADDR'], Config::getAppConfiguration('allowedAPiIPs')))) {
            Utils::logThis("ERROR", "IP '" . $_SERVER['REMOTE_ADDR'] . "' Not allowed to consume API" . serialize($credentials));
            $return['DESCRIPTION'] = "IP '" . $_SERVER['REMOTE_ADDR'] . "' Not allowed to consume API";
            return $return;
        }

        if (empty($payload)) {
            Utils::logThis("ERROR", "Payload Empty");
            $return['DESCRIPTION'] = "Payload Empty";
            return $return;
        }

        Utils::logThis("INFO", "Validate " . serialize($payload));

        switch ($payload) {

            case(!isset($payload['MSISDN']) && in_array($transactionType, array('B2C', 'ETOPUP'))):
                Utils::logThis("ERROR", "MSISDN not set");
                $return['DESCRIPTION'] = "MSISDN Not Set";

                break;

            case(!isset($payload['Command']) && in_array($transactionType, array('B2C'))):
                Utils::logThis("ERROR", "Command not set");
                $return['DESCRIPTION'] = "Command not set";

                break;

            case(!isset($payload['AMOUNT']) && in_array($transactionType, array('B2C', 'BusinessPayBill', 'BusinessBuyGoods', 'BusinessToBusinessTransfer', 'ETOPUP'))):
                Utils::logThis("ERROR", "AMOUNT not set");
                $return['DESCRIPTION'] = "AMOUNT not set";
                break;

            case(!isset($payload['REFID']) && in_array($transactionType, array('B2C', 'B2B', 'ETOPUP'))):
                Utils::logThis("ERROR", "REFID not set");

                $return['DESCRIPTION'] = "REFID not set";
                break;


            case(!isset($payload['RECEIVER']) && in_array($transactionType, array('AgencyRedistributionOfFloatFunds'))):
                Utils::logThis("ERROR", "RECEIVER not set");
                $return['DESCRIPTION'] = "RECEIVER not set";

                break;
            case((isset($payload['MSISDN']) and in_array($transactionType, array('B2C'))) and ! in_array($payload['MSISDN'], Config::getAppConfiguration('whiteListedB2CNumbers'))):
                Utils::logThis("ERROR", "MSISDN $payload[MSISDN] not whitelisted");
                $return['DESCRIPTION'] = "MSISDN $payload[MSISDN] not whitelisted";
                break;

            case((!isset($payload['RESULT_URL']) and in_array($transactionType, array('B2C')))):
                Utils::logThis("ERROR", "RESULT_URL not set");
                $return['DESCRIPTION'] = "RESULT_URL not set";
                break;

            case(in_array($transactionType, array('B2C')) and $payload['AMOUNT'] > Config::getAppConfiguration('maxB2CAmountAllowed')):
                Utils::logThis("ERROR", "AMOUNT $payload[AMOUNT] exeeds maximum allowed " . Config::getAppConfiguration('maxB2CAmountAllowed'));
                $return['DESCRIPTION'] = "AMOUNT $payload[AMOUNT] exeeds maximum allowed";
                break;

            case(in_array($transactionType, array('ETOPUP', 'B2C')) and strlen($payload['MSISDN']) < 12):
                Utils::logThis("ERROR", "MSISDN should be of length 12");
                $return['DESCRIPTION'] = "MSISDN should be of length 12";
                break;





            default:
                Utils::logThis("INFO", "All params Valid");
                $return['DESCRIPTION'] = "All params Valid";
                $return['STATUS'] = true;
        }

        Utils::logThis("INFO", "returning -- " . serialize($return));
        return $return;
    }

    public static function logThis($LEVEL, $logThis) {
        global $MSISDN;
        $logFile = "";
        switch ($LEVEL) {
            case "INFO":
                $logFile = Config::getAppConfiguration('infoLogFile');
                break;
            case "ERROR":
                $logFile = Config::getAppConfiguration('errorLogFile');
                break;
            case "DEBUG":
                $logFile = Config::getAppConfiguration('debugLogFile');
                break;
            default :
                $logFile = Config::getAppConfiguration('infoLogFile');
        }

        $e = new Exception();
        $trace = $e->getTrace();
        //position 0 would be the line that called this function so we ignore it
        $last_call = isset($trace[1]) ? $trace[1] : array();
        $lineArr = $trace[0];


        $function = isset($last_call['function']) ? $last_call['function'] . "()|" : "";
        $line = isset($lineArr['line']) ? $lineArr['line'] . "|" : "";
        $file = isset($lineArr['file']) ? $lineArr['file'] . "|" : "";

        $mobileNumber = strlen($MSISDN) > 0 ? $MSISDN . "|" : "";

        $remote_ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] . "|" : "";
        $date = date("Y-m-d H:i:s");
        $string = $date . "|$file$function$remote_ip$mobileNumber$line" . $logThis . "\n";
        file_put_contents($logFile, $string, FILE_APPEND);
    }

    public static function get_string_between($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) {
            return '';
        }
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public static function invokeSafaricomBroker($xml_request, $endPoint) {

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endPoint);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::getAppConfiguration('CurlConnectTimeOut'));
        curl_setopt($ch, CURLOPT_TIMEOUT, Config::getAppConfiguration('CurlReadTimeOut'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

        /* SSL options */

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        //curl_setopt($ch, CURLOPT_SSLCERT, Config::getAppConfiguration('SSL_CERT_PATH')); //liase with MPESA Team to get / generate the certs
        //curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        //curl_setopt($ch, CURLOPT_SSLKEY, Config::getAppConfiguration('SSL_KEY_PATH'));
        //curl_setopt($ch, CURLOPT_SSLKEYPASSWD, '');





        Utils::logThis("INFO", "about to send request to the endpoint::" . $endPoint);
        $result = curl_exec($ch);

        Utils::logThis("INFO", "Broker ACK::" . serialize($result));

        if (!$result) {
            Utils::logThis("ERROR", "erorrs if any " . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    public static function invokeSafaricomAirtimeAPI($xml_request, $endPoint) {

        $headers = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
        );

        $ch = curl_init();

        $endPoint.=urlencode($xml_request);
        curl_setopt($ch, CURLOPT_URL, $endPoint);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::getAppConfiguration('CurlConnectTimeOut'));
        curl_setopt($ch, CURLOPT_TIMEOUT, Config::getAppConfiguration('CurlReadTimeOut'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

        /* SSL options */

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);


        Utils::logThis("INFO", "about to send request to the endpoint::" . $endPoint);
        $result = curl_exec($ch);

        Utils::logThis("INFO", "endpoint ACK::" . serialize($result));

        if (!$result) {
            Utils::logThis("ERROR", "erorrs if any " . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    public static function AcknowledgeB2CResult() {
        $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:req="http://api-v1.gen.mm.vodafone.com/mminterface/request">
    <soapenv:Header/>
    <soapenv:Body>
        <req:ResponseMsg><![CDATA[<?xml version="1.0" encoding="UTF-8"?>
<response xmlns="http://api-v1.gen.mm.vodafone.com/mminterface/response">
    <ResponseCode>00000000</ResponseCode>
    <ResponseDesc>success</ResponseDesc>
</response>]]></req:ResponseMsg>
    </soapenv:Body>
</soapenv:Envelope>';

        echo $xml;
        Utils::logThis("DEBUG", "I have ACKed B2C result ");
    }

    public static function encryptPassword($password) {

        //  $pub_key = openssl_pkey_get_public(file_get_contents('Certs/apicrypt-staging.safaricom.co.ke.cer'));
        $pub_key = openssl_pkey_get_public(file_get_contents('Certs/ApiCryptPublicOnly.cer'));

        $pubKeyData = openssl_pkey_get_details($pub_key);

        $rsa = new Crypt_RSA();
        $rsa->loadKey($pubKeyData['key']); // public key


        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);

        $ciphertext = $rsa->encrypt($password);

        $securityCredential = base64_encode($ciphertext);

        return $securityCredential;
    }

    public static function processB2CResult($input) {
        $params = array();
        $params = array('STATUS' => 0);

        //now process the response
        $xml_cddata = Utils::get_string_between($input, "[CDATA[", "]]");

        if (strlen(trim($xml_cddata)) == 0) {
            Utils::logThis("ERROR", "no CDData in XML " . $input);
            exit;
        }
        $xml = simplexml_load_string($xml_cddata);

        if (!$xml) {
            Utils::logThis("ERROR", "Unable to load XML " . $xml_cddata);
            exit;
        }

        if (is_object($xml)) {
            $params['STATUS'] = 1;
            $params['ResultCode'] = (String) $xml->ResultCode;
            $params['ResultType'] = (String) $xml->ResultType;
            $params['ResultDesc'] = (String) $xml->ResultDesc;
            $params['OriginatorConversationID'] = (String) $xml->OriginatorConversationID;
            $params['ConversationID'] = (String) $xml->ConversationID;
            $params['TransactionID'] = (String) $xml->TransactionID;
            $ResultParameters = $xml->ResultParameters;


            if (isset($ResultParameters->ResultParameter)) {
                foreach ($ResultParameters->ResultParameter as $parameter) {
                    $key = (String) $parameter->Key;
                    $value = (String) $parameter->Value;
                    $params["$key"] = $value;
                }
            }
        }

        return $params;
    }

    public static function generateRefID() {
        
    }

    public function processC2BValidation($xml_request) {


        $xml = new SimpleXMLElement($xml_request);
        $xml_dom = dom_import_simplexml($xml);
        $nodelist = $xml_dom->getElementsByTagName('C2BPaymentValidationRequest');


        for ($i = 0; $i < $nodelist->length; $i++) {
            $transactionType = $nodelist->item($i)->getElementsByTagName('TransType')->item(0)->nodeValue;
            $transactionID = $nodelist->item($i)->getElementsByTagName('TransID')->item(0)->nodeValue;

            $TransTime = $nodelist->item($i)->getElementsByTagName('TransTime')->item(0)->nodeValue;
            $TransAmount = $nodelist->item($i)->getElementsByTagName('TransAmount')->item(0)->nodeValue;
            $BusinessShortCode = $nodelist->item($i)->getElementsByTagName('BusinessShortCode')->item(0)->nodeValue;
            $BillRefNumber = $nodelist->item($i)->getElementsByTagName('BillRefNumber')->item(0)->nodeValue;
            $InvoiceNumber = $nodelist->item($i)->getElementsByTagName('InvoiceNumber')->item(0)->nodeValue;
            $MSISDN = $nodelist->item($i)->getElementsByTagName('MSISDN')->item(0)->nodeValue;

            $names = "";
            foreach ($nodelist->item($i)->getElementsByTagname('KYCInfo') as $item) {
                $names.=$item->getElementsByTagName('KYCValue')->item(0)->nodeValue . " ";
            }
            $customerNames = ltrim($names);
        }
        Utils::logThis('INFO', "Extracted transactionType '$transactionType'|transactionID '$transactionID'| TransTime '$TransTime'|TransAmount '$TransAmount'|BusinessShortCode '$BusinessShortCode'|BillRefNumber '$BillRefNumber'|InvoiceNumber '$InvoiceNumber'|MSISDN '$MSISDN'|customerNames '$customerNames'");

        $ThirdPartyTransID = date('Ymdhis');

        if (strtolower($BillRefNumber) == 'invalidaccountno') {

            $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:c2b="http://cps.huawei.com/cpsinterface/c2bpayment">
   <soapenv:Header/>
   <soapenv:Body>
      <c2b:C2BPaymentValidationResult>
        <ResultCode>C2B00012</ResultCode>
	   <ResultDesc>Invalid account Number</ResultDesc>
	   <ThirdPartyTransID>' . $ThirdPartyTransID . '</ThirdPartyTransID>
      </c2b:C2BPaymentValidationResult>
   </soapenv:Body>
</soapenv:Envelope>';
            Utils::logThis("INFO", "Acknowleding Invalid Account C2B validation " . $xml);
            echo $xml;
        } else {

            $xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:c2b="http://cps.huawei.com/cpsinterface/c2bpayment">
   <soapenv:Header/>
   <soapenv:Body>
      <c2b:C2BPaymentValidationResult>
        <ResultCode>0</ResultCode>
	   <ResultDesc>Service processing successful</ResultDesc>
	   <ThirdPartyTransID>' . $ThirdPartyTransID . '</ThirdPartyTransID>
      </c2b:C2BPaymentValidationResult>
   </soapenv:Body>
</soapenv:Envelope>';
            Utils::logThis("INFO", "Acknowleding Successful C2B validation " . $xml);
            echo $xml;
        }
    }

    public function processC2BConfirmationRequest($xml_request) {

        try {
            $xml = new SimpleXMLElement($xml_request);
            $xml_dom = dom_import_simplexml($xml);
            $nodelist = $xml_dom->getElementsByTagName('C2BPaymentConfirmationRequest');


            for ($i = 0; $i < $nodelist->length; $i++) {
                $transactionType = $nodelist->item($i)->getElementsByTagName('TransType')->item(0)->nodeValue;
                $transactionID = $nodelist->item($i)->getElementsByTagName('TransID')->item(0)->nodeValue;

                $TransTime = $nodelist->item($i)->getElementsByTagName('TransTime')->item(0)->nodeValue;
                $TransAmount = $nodelist->item($i)->getElementsByTagName('TransAmount')->item(0)->nodeValue;
                $BusinessShortCode = $nodelist->item($i)->getElementsByTagName('BusinessShortCode')->item(0)->nodeValue;
                $BillRefNumber = $nodelist->item($i)->getElementsByTagName('BillRefNumber')->item(0)->nodeValue;
                $OrgAccountBalance = $nodelist->item($i)->getElementsByTagName('OrgAccountBalance')->item(0)->nodeValue;
                $MSISDN = $nodelist->item($i)->getElementsByTagName('MSISDN')->item(0)->nodeValue;
                $InvoiceNumber = '';
                $names = "";
                foreach ($nodelist->item($i)->getElementsByTagname('KYCInfo') as $item) {
                    $names.=$item->getElementsByTagName('KYCValue')->item(0)->nodeValue . " ";
                }
                $customerNames = ltrim($names);
            }
            Utils::logThis('INFO', "Extracted transactionType '$transactionType'|transactionID '$transactionID'| TransTime '$TransTime'|TransAmount '$TransAmount'|BusinessShortCode '$BusinessShortCode'|BillRefNumber '$BillRefNumber'|OrgAccountBalance '$OrgAccountBalance'|MSISDN '$MSISDN'|customerNames '$customerNames'");


            /*$dbPkID = Utils::saveTransactionToDb("C2B", $MSISDN, $TransAmount, $transactionID, "$BillRefNumber", 0, $customerNames);
            if (!$dbPkID) {
                //dont know what to do
                Utils::logThis("ERROR", "unable to save C2B transaction to DB");
                return $transactionID;
            }
			
			*/ 

            Utils::logThis("DEBUG", "About to send C2B trx to ISO server listenr");

            //$MSISDN, $amount, $refID, $receiptNo, $destinationAccount
            if (!Utils::sendC2BRequestToISOServer($MSISDN, $TransAmount, $dbPkID, $transactionID, $BillRefNumber)) {
                Utils::logThis("ERROR", "unable to send C2B transaction to ISO Bridge. Save or later retry |msisdn$MSISDN|$TransAmount|$dbPkID|$BillRefNumber|$InvoiceNumber");
                //Utils::updateDbTransactionStatus('ID', $dbPkID, array('STATUS' => 3));
                return $transactionID;
            }
//sent OK .. save sent status
            //Utils::updateDbTransactionStatus('ID', $dbPkID, array('STATUS' => 1));

            return $transactionID;
        } catch (Exception $ex) {
            Utils::logThis("ERROR", "Excption " . $ex->getMessage());
        }
    }

    public static function updateDbTransactionStatus($searchColumn, $value, $payload) {
		
		//database configurations
		  $db_host= "localhost";
		  $db_name= "mpesaapi"; 
		  $db_username = "std_db_ureport"; 
		  $db_password = "owesome2014!!!!"; 

        $updateString = "";
        foreach ($payload as $key => $val) {
            $updateString.="$key='$val',";
        }
        $newString = rtrim($updateString, ",");
        $sql = "update transactions set $newString where $searchColumn='$value'";

        Utils::logThis("INFO", 'query ' . $sql);
		
		$updatedRow = mysql_query($sql) or die(mysql_error());
  
        return $updatedRow;
      
    }

    public static function pushB2CResult($resultArray) {

        Utils::logThis("DEBUG", "got " . serialize($resultArray));
        $pkArr = explode("_", $resultArray['OriginatorConversationID']);
        $pkID = end($pkArr);

        $resultArray['REFID'] = $pkArr[count($pkArr) - 2];
        $url = Utils::getSqliteDatabaseValue($pkID, "RESULT_URL");

        $string = "";
        foreach ($resultArray as $key => $value) {
            $string.=$key . "=" . urlencode($value) . "&";
        }
        $finalString = rtrim($string, "&");
        $resultUrl = $url . "?" . $finalString;
        Utils::logThis("INFO", "RESULT URL " . $resultUrl);
        if (strlen($url) > 0) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $resultUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            Utils::logThis("INFO", "RESULT URL response " . $response);
        } else {
            Utils::logThis("DEBUG", "NO B2C Results URL");
        }
        //get URL saved for this transaction
    }

    public static function saveTransactionToDb($TRANSACTION_TYPE, $MSISDN, $AMOUNT, $BROKER_REFID, $OUR_REFID, $STATUS, $ADDITIONAL_DETAILS, $RESULT_URL = '') {

        Utils::logThis("DEBUG", "Called to save transaction values:TRANSACTION_TYPE '$TRANSACTION_TYPE'|MSISDN '$MSISDN'| AMOUNT'$AMOUNT'|BROKER_REFID '$BROKER_REFID'|OUR_REFID '$OUR_REFID'|STATUS '$STATUS'|ADDITIONAL_DETAILS '$ADDITIONAL_DETAILS'|RESULT_URL '$RESULT_URL'");
        /**/
       //database configurations
		$db_host= "localhost";
		$db_name= "mpesaapi"; 
		$db_username = "std_db_ureport"; //std_db_ureport
		$db_password = "owesome2014!!!!"; //owesome2014!!!!


		//open database connection
		$connection=mysql_connect($db_host,$db_username,$db_password)or die('Could not connect to the database : '.mysql_error());
		mysql_select_db($db_name) or die('Could not select the database : '.mysql_error());
		date_default_timezone_set('Africa/Nairobi');

        $today = date('Y-m-d H:i:s');
		
		$sql = "insert into transactions (MSISDN,TRANSACTION_TYPE,AMOUNT,MPESA_REF_ID,OUR_REFID,STATUS,ADDITIONAL_DETAILS,RESULT_URL,DATE_CREATED,DATE_MODIFIED) values
		('$MSISDN','$TRANSACTION_TYPE','$AMOUNT','$BROKER_REFID','$OUR_REFID','$STATUS','$ADDITIONAL_DETAILS','$RESULT_URL','$today','$today')";
		
		
		$insertedRow = mysql_query($sql) or die(mysql_error());
		
		return $insertedRow;
			
			
	}

    public static function getSqliteDatabaseValue($pkID, $col) {

        try {
            $file_db = new PDO('sqlite:' . Config::getAppConfiguration('transactionDBFile'));
            $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "select $col from TRANSACTIONS where ID='$pkID'";

            Utils::logThis("DEBUG", "query " . $query);

            $result = $file_db->query($query);

            if (count($result) > 0) {
                Utils::logThis("DEBUG", "there were '" . count($result) . "'" . $query . "'");
                foreach ($result as $row) {
                    
                }

                $return = $row[$col];
                Utils::logThis("DEBUG", "returning $return");
                return $return;
            }
            return false;
        } catch (Exception $ex) {
            Utils::logThis("ERROR", "Exception " . $ex->getMessage());
        }
    }

    function processAirtimeResponse($xml_string) {

        $type = "";
        $transactionStat = "";
        $refID = "";
        $etopUpTrxID = "";
        $message = "";
        $date = "";
        $xml_string = preg_replace('/(<\?xml[^?]+?)utf-16/i', '$1UTF-8', $xml_string);
        try {
            $xml = simplexml_load_string($xml_string);
            Utils::logThis("INFO", "XML Object::" . print_r($xml_string, true));
            // $xml->registerXPathNamespace('result', 'http://safaricom.co.ke/Pinless/ErrorSchema.xsd');
            $xml->registerXPathNamespace('result', 'http://www.tibco.com/schemas/pinless/PINLESS.core/C2STransferBillPayment/Schema.xsd9');
            $type = (String) $xml->xpath('//result:TYPE')[0][0];
            $transactionStat = (String) $xml->xpath('//result:TXNSTATUS')[0][0];
            $refID = (String) $xml->xpath('//result:EXTREFNUM')[0][0];
            $etopUpTrxID = (String) $xml->xpath('//result:TXNID')[0][0];
            $message = (String) $xml->xpath('//result:MESSAGE')[0][0];
            $date = (String) $xml->xpath('//result:DATE')[0][0];
        } catch (Exception $ex) {
            Utils::logThis("INFO", "Exception " . $ex->getMessage());
        }
        if ($transactionStat == "200") {
            Utils::logThis("INFO", "API Call OK response code " . $transactionStat);
            $response["STATUS"] = 1;
            $response["DESCRIPTION"] = $message;
            $response["RESPONSE_DATA"] = array('REFID' => $refID, 'ETOPUPTRXID' => $etopUpTrxID, 'DATE' => $date);
        } else {

            Utils::logThis("INFO", "API Call NOK response code " . $transactionStat);
            $response["STATUS"] = 0;
            $response["DESCRIPTION"] = $message;
            $response["RESPONSE_DATA"] = $refID;
        }
        
        return $response;
    }

}
