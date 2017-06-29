<?php

include 'IXR_Library.inc.php';
include 'Classes/Utils.php';
include 'Classes/Config.php';
include('Crypt/RSA.php');
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');

include('Net/SSH2.php');

Class SafaricomAPIs extends IXR_Server {

    /**
     * Constructor
     */
    function __construct() {

        $this->IXR_Server(array(
            'SAFARICOM.makeB2BRequest' => 'this:makeB2BRequest',
            'SAFARICOM.registerC2BUrls' => 'this:registerC2BUrl',
            'SAFARICOM.queryB2CTransactionStatus' => 'this:queryB2CTrxStatus',
            'SAFARICOM.makeAirtimeRequest' => 'this:makeAirtimeRequest',
        ));
    }

    /**
     * This function gets recieves request paramemeters and invokes the Safaricom gateway to make a B2C request
     *
     * @param  MSISDN, AMOUNT
     * @return STATUS
     */
    function makeB2CRequest($request) {
        $status = 0;
        $response = array();
        $data = array();

        $credentials = $request['credentials'];
        $payload = $request['payload'];

        $description = "";

        Utils::logThis("INFO", __METHOD__ . "|received request " . print_r($request, true));


        $validPayload = Utils::validatePayload($credentials, $payload, 'B2C');
        if (isset($validPayload['STATUS']) and $validPayload['STATUS'] == TRUE) {
            Utils::logThis("INFO", __METHOD__ . "|payload items valid");

            //now insert the transaction details to db
            $pkID = Utils::saveTransactionToDb("B2C", $payload['MSISDN'], $payload['AMOUNT'], '', $payload['REFID'], 0, '', $payload['RESULT_URL']);
            if (!$pkID) {

                $response = array(
                    "STATUS" => 0,
                    "DESCRIPTION" => "Unable to Save transaction to database",
                    "RESPONSE_DATA" => ""
                );
                Utils::logThis("ERROR", "was unable to save trx to db. dont proceed " . serialize($response));

                return $response;
            }
            $initiatorSecurityCredential = Utils::encryptPassword(Config::getAppConfiguration("B2CinitiatorPassword"));

            $timeStamp = date('YmdHis');
            $originalConversationID = Config::getAppConfiguration('B2CShortcode') . "_MTECH_" . date('YmdHis') . "_" . $payload['REFID'] . "_" . $pkID;
            Utils::logThis("INFO", __METHOD__ . "originalConversationID|$originalConversationID");
            $spPassword = base64_encode(hash('sha256', Config::getAppConfiguration('B2CspID') . "" . Config::getAppConfiguration('B2Cpassword') . "" . $timeStamp));




            $xml_request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:req="http://api-v1.gen.mm.vodafone.com/mminterface/request">
   <soapenv:Header>
      <tns:RequestSOAPHeader xmlns:tns="http://www.huawei.com/schema/osg/common/v2_1">
         <tns:spId>' . Config::getAppConfiguration('B2CspID') . '</tns:spId>
         <tns:spPassword>' . $spPassword . '</tns:spPassword>
         <tns:timeStamp>' . $timeStamp . '</tns:timeStamp>
         <tns:serviceId>' . Config::getAppConfiguration('B2CserviceID') . '</tns:serviceId>
      </tns:RequestSOAPHeader>
   </soapenv:Header>
   <soapenv:Body>
      <req:RequestMsg>
      <![CDATA[<?xml version="1.0" encoding="UTF-8"?>
      <request xmlns="http://api-v1.gen.mm.vodafone.com/mminterface/request">
<Transaction>
        <CommandID>' . $payload['Command'] . '</CommandID>
        <LanguageCode>0</LanguageCode>
        <OriginatorConversationID>' . $originalConversationID . '</OriginatorConversationID>
        <ConversationID></ConversationID>
        <Remark>0</Remark>
<Parameters><Parameter>
        <Key>Amount</Key>
        <Value>' . $payload['AMOUNT'] . '</Value>
</Parameter></Parameters>
<ReferenceData>
        <ReferenceItem>
                <Key>QueueTimeoutURL</Key>
                <Value>' . Config::getAppConfiguration('timeOutUrl') . '</Value>
</ReferenceItem></ReferenceData>
        <Timestamp>' . $timeStamp . '</Timestamp>
</Transaction>
<Identity>
        <Caller>
                <CallerType>2</CallerType>
                <ThirdPartyID>' . Config::getAppConfiguration('B2Cinitiator') . '</ThirdPartyID>
                <Password>' . Config::getAppConfiguration('B2CinitiatorPassword') . '</Password>
                <CheckSum>null</CheckSum>
                <ResultURL>' . Config::getAppConfiguration('resultUrl') . '</ResultURL>
        </Caller>
        <Initiator>
               <IdentifierType>11</IdentifierType>
                   <Identifier>' . Config::getAppConfiguration('B2Cinitiator') . '</Identifier>
                  <SecurityCredential>' . $initiatorSecurityCredential . '</SecurityCredential>
                  <ShortCode>' . Config::getAppConfiguration('B2CShortcode') . '</ShortCode>
        </Initiator>
                <PrimaryParty>
                        <IdentifierType>4</IdentifierType>
         <Identifier>' . Config::getAppConfiguration('B2CShortcode') . '</Identifier>
                        <ShortCode>' . Config::getAppConfiguration('B2CShortcode') . '</ShortCode>
                </PrimaryParty>
        <ReceiverParty>
                <IdentifierType>1</IdentifierType>
                <Identifier>' . $payload['MSISDN'] . '</Identifier>
                <ShortCode>' . Config::getAppConfiguration('B2CShortcode') . '</ShortCode>
        </ReceiverParty>
        <AccessDevice>
                <IdentifierType>4</IdentifierType>
                <Identifier>' . Config::getAppConfiguration('B2CShortcode') . '</Identifier>
         </AccessDevice>
 </Identity>
         <KeyOwner>1</KeyOwner>
        </request>]]></req:RequestMsg>
   </soapenv:Body>
</soapenv:Envelope>';


            Utils::logThis("INFO", "request---" . $xml_request);

            $result = Utils::invokeSafaricomBroker($xml_request, Config::getAppConfiguration('brokerEndPointURL'));

            if ($result) {

                //now process the response
                $xml_cddata = Utils::get_string_between($result, "[CDATA[", "]]");
                $xml = simplexml_load_string($xml_cddata);
                Utils::logThis("INFO", "XML Object::" . print_r($xml, true));
                $responseArray = (array) $xml;
                if ($responseArray['ResponseCode'] == 0) {
                    Utils::logThis("INFO", "API Call OK response code " . $responseArray['ResponseCode']);
                    $response["STATUS"] = 1;
                    $response["DESCRIPTION"] = $responseArray['ResponseDesc'];
                    $response["REF_ID"] = $responseArray['OriginatorConversationID'];
                    $response["MPESA_REF_ID"] = $responseArray['ConversationID'];
                    Utils::updateDbTransactionStatus('ID', $pkID, array('OUR_REFID' => $responseArray['OriginatorConversationID'], 'MPESA_REF_ID' => $responseArray['ConversationID']));
                } else {

                    Utils::logThis("INFO", "API Call NOK response code " . $responseArray['ResponseCode']);
                    $response["STATUS"] = 0;
                    $response["DESCRIPTION"] = $responseArray['ResponseDesc'];
                    $response["RESPONSE_DATA"] = $responseArray['OriginatorConversationID'];
                }
            } else {
                $response["STATUS"] = 0;
                $response["DESCRIPTION"] = "Failed to send to broker";
                $response["RESPONSE_DATA"] = "";
            }
        } else {

            $status = 0;
            $description = $validPayload['DESCRIPTION'];
            $data = null;
            Utils::logThis("ERROR", __METHOD__ . "|$description::" . serialize($payload));

            return $response = array(
                "STATUS" => 0,
                "DESCRIPTION" => $description,
                "RESPONSE_DATA" => $data
            );
        }



        Utils::logThis("INFO", __METHOD__ . "|Response being returned to the client: " . print_r($response, true));


        return $response;
    }

    function makeB2BRequest($request) {
        $status = 0;
        $response = array();
        $data = array();

        $credentials = $request['credentials'];
        $payload = $request['payload'];

        $description = "";
        $accountReference = isset($payload['AccountReference']) ? $payload['AccountReference'] : $payload['AMOUNT'];
        Utils::logThis("INFO", __METHOD__ . "|received request " . print_r($request, true));


        $validPayload = Utils::validatePayload($credentials, $payload, $payload['COMMAND']);
        if ($validPayload == TRUE) {
            Utils::logThis("INFO", __METHOD__ . "|payload items valid");


            $initiatorSecurityCredential = Utils::encryptPassword(Config::getAppConfiguration("B2BinitiatorPassword"));

            $timeStamp = date('YmdHis');
            $originalConversationID = Config::getAppConfiguration('B2Bshortcode') . "_MTECH_" . date('YmdHis') . "_" . $payload['REFID'];
            Utils::logThis("INFO", __METHOD__ . "originalConversationID|$originalConversationID");
            $spPassword = base64_encode(hash('sha256', Config::getAppConfiguration('spID') . "" . Config::getAppConfiguration('password') . "" . $timeStamp));


            $paramsStructure = '';

            if (isset($payload['PARAMS']) and is_array($payload['PARAMS'])) {
                foreach ($payload['PARAMS'] as $paramKey => $paramValue) {
                    $paramsStructure.= '<Parameter><Key>' . $paramKey . '</Key>'
                            . '<Value>' . $paramValue . '</Value>'
                            . '</Parameter>';
                }
            }


            $xml_request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:req="http://api-v1.gen.mm.vodafone.com/mminterface/request">
    <soapenv:Header>
        <tns:RequestSOAPHeader xmlns:tns="http://www.huawei.com/schema/osg/common/v2_1">
            <tns:spId>' . Config::getAppConfiguration('spID') . '</tns:spId>
            <tns:spPassword>' . $spPassword . '</tns:spPassword>
            <tns:timeStamp>' . $timeStamp . '</tns:timeStamp>
            <tns:serviceId>' . Config::getAppConfiguration('serviceID') . '</tns:serviceId>
        </tns:RequestSOAPHeader>
    </soapenv:Header>
    <soapenv:Body>
        <req:RequestMsg><![CDATA[
<?xml version="1.0" encoding="UTF-8"?><request xmlns="http://api-v1.gen.mm.vodafone.com/mminterface/request">
    <Transaction>
        <CommandID>' . $payload['COMMAND'] . '</CommandID>
        <LanguageCode>0</LanguageCode>
        <OriginatorConversationID>' . $originalConversationID . '</OriginatorConversationID>
        <ConversationID></ConversationID>
        <Remark>0</Remark>
        <Parameters>
            ' . $paramsStructure . '
            </Parameters>
        <ReferenceData>
            <ReferenceItem>
                <Key>QueueTimeoutURL</Key>
                <Value>' . Config::getAppConfiguration('timeOutUrl') . '</Value>
            </ReferenceItem>
        </ReferenceData>
        <Timestamp>' . $timeStamp . '</Timestamp>
    </Transaction>
    <Identity>
        <Caller>
            <CallerType>2</CallerType>
            <ThirdPartyID>' . Config::getAppConfiguration('B2Binitiator') . '</ThirdPartyID>
            <Password>' . Config::getAppConfiguration('B2BinitiatorPassword') . '</Password>
            <CheckSum>null</CheckSum>
            <ResultURL>' . Config::getAppConfiguration('B2BresultUrl') . '</ResultURL>
        </Caller>
        <Initiator>
            <IdentifierType>11</IdentifierType>
             <Identifier>' . Config::getAppConfiguration('B2Binitiator') . '</Identifier>
                  <SecurityCredential>' . $initiatorSecurityCredential . '</SecurityCredential>
                  <ShortCode>' . Config::getAppConfiguration('B2Bshortcode') . '</ShortCode>
        </Initiator>
<PrimaryParty>
            <IdentifierType>4</IdentifierType>
            <Identifier>' . Config::getAppConfiguration('B2Bshortcode') . '</Identifier>
            <ShortCode>' . Config::getAppConfiguration('B2Bshortcode') . '</ShortCode>
            </PrimaryParty>        
<ReceiverParty>
            <IdentifierType>4</IdentifierType>
            <Identifier>' . $payload['RECEIVER'] . '</Identifier>
            <ShortCode>' . $payload['RECEIVER'] . '</ShortCode>
        </ReceiverParty>
        <AccessDevice>
            <IdentifierType>4</IdentifierType>
            <Identifier>1</Identifier>
        </AccessDevice>
    </Identity>
    <KeyOwner>1</KeyOwner>
</request>]]></req:RequestMsg>
    </soapenv:Body>
</soapenv:Envelope>';

            Utils::logThis("INFO", "request---" . $xml_request);

            $result = Utils::invokeSafaricomBroker($xml_request, Config::getAppConfiguration('brokerEndPointURL'));

            if ($result) {

                //now process the response
                $xml_cddata = Utils::get_string_between($result, "[CDATA[", "]]");
                $xml = simplexml_load_string($xml_cddata);
                Utils::logThis("INFO", "XML Object::" . print_r($xml, true));
                $responseArray = (array) $xml;
                if ($responseArray['ResponseCode'] == 0) {
                    Utils::logThis("INFO", "API Call OK response code " . $responseArray['ResponseCode']);
                    $response["STATUS"] = 1;
                    $response["DESCRIPTION"] = $responseArray['ResponseDesc'];
                    $response["RESPONSE_DATA"] = $responseArray['OriginatorConversationID'];
                } else {

                    Utils::logThis("INFO", "API Call NOK response code " . $responseArray['ResponseCode']);
                    $response["STATUS"] = 0;
                    $response["DESCRIPTION"] = $responseArray['ResponseDesc'];
                    $response["RESPONSE_DATA"] = $responseArray['OriginatorConversationID'];
                }
            } else {
                $response["STATUS"] = 0;
                $response["DESCRIPTION"] = "Failed to send to broker";
                $response["RESPONSE_DATA"] = "";
            }
        } else {

            $status = 0;
            $description = "Invalid Parameters provided";
            $data = null;
            Utils::logThis("ERROR", __METHOD__ . "|invalid parameters provided::" . serialize($payload));

            return $response = array(
                "STATUS" => 0,
                "DESCRIPTION" => $description,
                "RESPONSE_DATA" => $data
            );
        }



        Utils::logThis("INFO", __METHOD__ . "|Response being returned to the client: " . print_r($response, true));


        return $response;
    }

    function registerC2BUrl() {

        $timeStamp = date('YmdHis');
        $originalConversationID = Config::getAppConfiguration('shortcode') . "_MTECH_" . date('YmdHis') . "_" . "henry";
        Utils::logThis("INFO", "originalConversationID|$originalConversationID");
        $spPassword = base64_encode(hash('sha256', Config::getAppConfiguration('C2BSpID') . "" . Config::getAppConfiguration('C2Bpassword') . "" . $timeStamp));



        $xml_request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                  xmlns:req="http://api-v1.gen.mm.vodafone.com/mminterface/request">
    <soapenv:Header>
        <tns:RequestSOAPHeader xmlns:tns="http://www.huawei.com/schema/osg/common/v2_1">
            <tns:spId>' . Config::getAppConfiguration('C2BSpID') . '</tns:spId>
            <tns:spPassword>' . $spPassword . '</tns:spPassword>
            <tns:timeStamp>' . $timeStamp . '</tns:timeStamp>
            <tns:serviceId>' . Config::getAppConfiguration('C2BServiceID') . '</tns:serviceId>
        </tns:RequestSOAPHeader>
    </soapenv:Header>
    <soapenv:Body>
        <req:RequestMsg><![CDATA[<?xml version="1.0" encoding="UTF-8"?>
<request xmlns="http://api-v1.gen.mm.vodafone.com/mminterface/request">
    <Transaction>
        <CommandID>RegisterURL</CommandID>
        <OriginatorConversationID>' . $originalConversationID . '</OriginatorConversationID>
        <Parameters>
            <Parameter>
                <Key>ResponseType</Key>
                <Value>Completed</Value>
            </Parameter>
        </Parameters>
        <ReferenceData>
            <ReferenceItem>
                <Key>ValidationURL</Key>
                <Value>' . Config::getAppConfiguration('C2BvalidationUrl') . '</Value>
            </ReferenceItem>
            <ReferenceItem>
                <Key>ConfirmationURL</Key>
                <Value>' . Config::getAppConfiguration('C2BconfirmationURL') . '</Value>
            </ReferenceItem>
        </ReferenceData>
    </Transaction>
    <Identity>
        <Caller>
            <CallerType>0</CallerType>
            <ThirdPartyID/>
            <Password/>
            <CheckSum/>
            <ResultURL/>
        </Caller>
        <Initiator>
            <IdentifierType>1</IdentifierType>
            <Identifier/>
            <SecurityCredential/>
            <ShortCode>' . Config::getAppConfiguration('C2Bshortcode') . '</ShortCode>
        </Initiator>
        <PrimaryParty>
            <IdentifierType>1</IdentifierType>
            <Identifier/>
            <ShortCode>' . Config::getAppConfiguration('C2Bshortcode') . '</ShortCode>
        </PrimaryParty>
    </Identity>
    <KeyOwner>1</KeyOwner>
</request>]]></req:RequestMsg>
    </soapenv:Body>
</soapenv:Envelope>';



        Utils::logThis("INFO", "request---" . $xml_request);

        $result = Utils::invokeSafaricomBroker($xml_request, Config::getAppConfiguration('c2BRegisterEndPointUrl'));

        if ($result) {

            //now process the response
            $xml_cddata = Utils::get_string_between($result, "[CDATA[", "]]");
            $xml = simplexml_load_string($xml_cddata);
            Utils::logThis("INFO", "XML Object::" . print_r($xml, true));
            $responseArray = (array) $xml;
            if ($responseArray['ResponseCode'] == 0) {
                Utils::logThis("INFO", "API Call OK response code " . $responseArray['ResponseCode']);
                $response["STATUS"] = 1;
                $response["DESCRIPTION"] = $responseArray['ResponseDesc'];
                $response["RESPONSE_DATA"] = $responseArray['OriginatorConversationID'];
            } else {

                Utils::logThis("INFO", "API Call NOK response code " . $responseArray['ResponseCode']);
                $response["STATUS"] = 0;
                $response["DESCRIPTION"] = $responseArray['ResponseDesc'];
                $response["RESPONSE_DATA"] = $responseArray['OriginatorConversationID'];
            }
        } else {
            $response["STATUS"] = 0;
            $response["DESCRIPTION"] = "Failed to send to broker";
            $response["RESPONSE_DATA"] = "";
        }

        return $response;
    }

    function queryB2CTrxStatus($request) {


        $credentials = $request['credentials'];
        $payload = $request['payload'];

        $description = "";

        Utils::logThis("INFO", __METHOD__ . "|received request " . print_r($request, true));


        $validPayload = Utils::validatePayload($credentials, $payload, 'queryTransactionStatus');
        if ($validPayload == TRUE) {


            $timeStamp = date('YmdHis');
            $originalConversationID = $payload['originalConversationID'];
            Utils::logThis("INFO", __METHOD__ . "originalConversationID|$originalConversationID");
            $spPassword = base64_encode(hash('sha256', Config::getAppConfiguration('spID') . "" . Config::getAppConfiguration('password') . "" . $timeStamp));

            $xml_request = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v2="http://www.huawei.com.cn/schema/common/v2_1" xmlns:loc="http://www.csapi.org/schema/transaction/data/v1_0/local" xmlns:res="http://api-v1.gen.mm.vodafone.com/mminterface/result">
    <soapenv:Header>
        <v2:RequestSOAPHeader>
            <v2:spId>' . Config::getAppConfiguration('spID') . '</v2:spId>
            <v2:spPassword>' . $spPassword . '</v2:spPassword>
            <v2:serviceId>' . Config::getAppConfiguration('serviceID') . '</v2:serviceId>
            <v2:timeStamp>' . $timeStamp . '</v2:timeStamp>
        </v2:RequestSOAPHeader>
    </soapenv:Header>
    <soapenv:Body>
        <loc:queryTransaction>
            <loc:originatorConversationID>' . $originalConversationID . '</loc:originatorConversationID>
            <loc:extensionInfo>
                <loc:item>
                    <res:Key>queryDate</res:Key>
                    <res:Value>' . $timeStamp . '</res:Value>
                </loc:item>
            </loc:extensionInfo>
        </loc:queryTransaction>
    </soapenv:Body>
';

            Utils::logThis("INFO", "request---" . $xml_request);

            $result = Utils::invokeSafaricomBroker($xml_request, Config::getAppConfiguration('queryB2CTrxStatusEndPoint'));

            if ($result) {

                $xml = new SimpleXMLElement($result);
                $xml_dom = dom_import_simplexml($xml);
                $nodelist = $xml_dom->getElementsByTagName('queryTransactionResponse');

                for ($i = 0; $i < $nodelist->length; $i++) {
                    $responseCode = $nodelist->item($i)->getElementsByTagName('ResponseCode')->item(0)->nodeValue;
                    // $responseDescription = $nodelist->item($i)->getElementsByTagName('ResponseDesc')->item(0)->nodeValue;
                    // $submitApiRequestList = $nodelist->item($i)->getElementsByTagName('submitApiRequest')->item(0)->nodeValue;
                    $submitApiResult = isset($nodelist->item($i)->getElementsByTagName('submitApiResult')->item(0)->nodeValue) ? $nodelist->item($i)->getElementsByTagName('submitApiResult')->item(0)->nodeValue : "";

                    $submitApiResultXML = base64_decode($submitApiResult);
                }
                Utils::logThis('INFO', 'responseCOde ' . $responseCode);
                if ($responseCode == 0) {
                    Utils::logThis("INFO", "API Call OK response code " . $xml->ResponseCode);
                    $response["STATUS"] = 1;
                    $response["DESCRIPTION"] = $xml->ResponseDesc;
                    $response["RESPONSE_DATA"] = strlen($submitApiResultXML) > 0 ? Utils::processB2CResult($submitApiResultXML) : "";
                } else {

                    Utils::logThis("INFO", "API Call NOK response code " . $xml->ResponseCode);
                    $response["STATUS"] = 0;
                    $response["DESCRIPTION"] = $xml->ResponseDesc;
                    $response["RESPONSE_DATA"] = '';
                }
            } else {
                $response["STATUS"] = 0;
                $response["DESCRIPTION"] = "Failed to send to broker";
                $response["RESPONSE_DATA"] = "";
            }
        } else {
            $status = 0;
            $description = "Invalid Parameters provided";
            $data = null;
            Utils::logThis("ERROR", __METHOD__ . "|invalid parameters provided::" . serialize($payload));

            return $response = array(
                "STATUS" => 0,
                "DESCRIPTION" => $description,
                "RESPONSE_DATA" => $data
            );
        }
        return $response;
    }

    function makeAirtimeRequest($request) {
        $status = 0;
        $response = array();
        $data = array();

        $credentials = $request['credentials'];
        $payload = $request['payload'];

        $description = "";
        Utils::logThis("INFO", __METHOD__ . "|received request " . print_r($request, true));


        $validPayload = Utils::validatePayload($credentials, $payload, "ETOPUP");
        if (isset($validPayload['STATUS']) and $validPayload['STATUS'] == TRUE) {
            Utils::logThis("INFO", __METHOD__ . "|payload items valid");


            //now insert the transaction details to db
            $pkID = Utils::saveTransactionToDb("ETOPUP", $payload['MSISDN'], $payload['AMOUNT'], '', $payload['REFID'], 0, '', $payload['RESULT_URL']);
            if (!$pkID) {

                $response = array(
                    "STATUS" => 0,
                    "DESCRIPTION" => "Unable to Save transaction to database",
                    "RESPONSE_DATA" => ""
                );
                Utils::logThis("ERROR", "was unable to save trx to db. dont proceed " . serialize($response));

                return $response;
            }



            $xml_request = '<?xml version="1.0" encoding="UTF-8"?>'
                    . '<ns0:COMMAND xmlns:ns0="http://safaricom.co.ke/Pinless/keyaccounts/">'
                    . '<ns0:TYPE>EXRCTRFREQ</ns0:TYPE>'
                    . '<ns0:DATE>' . date('d-M-Y') . '</ns0:DATE>'
                    . '<ns0:EXTNWCODE>SA</ns0:EXTNWCODE>'
                    . '<ns0:MSISDN>' . Config::getAppConfiguration('etopUpMSISDN') . '</ns0:MSISDN>'
                    . '<ns0:PIN>' . Config::getAppConfiguration('etopUpPin') . '</ns0:PIN>'
                    . '<ns0:LOGINID>' . Config::getAppConfiguration('etopUpDealerCode') . '</ns0:LOGINID>'
                    . '<ns0:PASSWORD>' . Config::getAppConfiguration('etopUpPassword') . '</ns0:PASSWORD>'
                    . '<ns0:EXTCODE>' . Config::getAppConfiguration('etopUpDealerCode') . '</ns0:EXTCODE>'
                    . '<ns0:EXTREFNUM>' . $payload['REFID'] . '</ns0:EXTREFNUM>'
                    . '<ns0:MSISDN2>' . substr($payload['MSISDN'], 3) . '</ns0:MSISDN2>'
                    . '<ns0:AMOUNT>' . $payload['AMOUNT'] * 100 . '</ns0:AMOUNT>'
                    . '<ns0:LANGUAGE1></ns0:LANGUAGE1>'
                    . '<ns0:LANGUAGE2></ns0:LANGUAGE2>'
                    . '<ns0:SELECTOR></ns0:SELECTOR>'
                    . '</ns0:COMMAND>';

            Utils::logThis("INFO", "request---" . $xml_request);

            $result = Utils::invokeSafaricomAirtimeAPI($xml_request, Config::getAppConfiguration('etopUpEndPointURL'));

            if ($result) {

                //now process the response
                $response = Utils::processAirtimeResponse($result);
            } else {
                $response["STATUS"] = 0;
                $response["DESCRIPTION"] = "Failed to send to endpoint";
                $response["RESPONSE_DATA"] = "";
            }
        } else {

            $status = 0;
            $description = $validPayload['DESCRIPTION'];
            $data = null;
            Utils::logThis("ERROR", __METHOD__ . "|$validPayload[DESCRIPTION]::" . serialize($payload));

            return $response = array(
                "STATUS" => 0,
                "DESCRIPTION" => $description,
                "RESPONSE_DATA" => $data
            );
        }



        Utils::logThis("INFO", __METHOD__ . "|Response being returned to the client: " . print_r($response, true));


        return $response;
    }

}

//instantiate the server
$server = new SafaricomAPIs();
