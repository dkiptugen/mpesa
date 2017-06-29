<?php

/**
 * Description of newPHPClass
 *
 * @author @elijakey
 */
class Config {

    //put your code here
    public static function getAppConfiguration($config, $defaultIfNotExist = FALSE) {
        $configurations = array(
            /* Global Configurations */
            'allowedAPiIPs' => array('37.188.98.230'),
            'infoLogFile' => '/var/log/applications/mpesa/info.log',
            'errorLogFile' => '/var/log/applications/mpesa/error.log',
            'debugLogFile' => '/var/log/applications/mpesa/debug.log',
            'transactionDBFile' => '/home/std.co.ke/mpesaB2C/DB/TRANSACTIONS.db',
            'CurlConnectTimeOut' => 30,
            'CurlReadTimeOut' => 40,
            'debug' => 1,
            /* End of global Configurations */

            /* B2C Configurations */
            'brokerEndPointURL' => "https://196.201.214.137:18423/mminterface/request",
            'timeOutUrl' => "https://192.168.0.9:8310/timeout/B2C/",
            'resultUrl' => "https://192.168.0.9:8310/result/B2C/",
            'B2CspID' => '#####',
            'B2CShortcode' => '####',
            'B2Cpassword' => "#####",
            'B2CserviceID' => '####',
            'username' => 'apiUser',
            'B2Cinitiator' => 'apiUser',
            'B2CinitiatorPassword' => "#####",
            'whiteListedB2CNumbers' => array(254726789778, 254725174083),
            'maxB2CAmountAllowed' => 9,
            /* End of B2C Configurations */

            /* Query B2C Trx Configurations */
            'queryB2CTrxStatusEndPoint' => 'http://196.201.214.136:8310/queryTransactionService/services/transaction',
            /* End of queryB2CTrxStatus Configurations */

            /* SSL Configurations */
            'SSL_CERT_PATH' => '/home/std.co.ke/mpesa/Certs/std.pem',
            'SSL_KEY_PATH' => '/home/std.co.ke/mpesa/Certs/sms.ktnkenya.com.key',
            /* End of SSL Configurations */

            /* C2B Configurations */
            'C2BSpID' => '107015',
            'C2BvalidationUrl' => "https://192.168.0.9:8310/C2B/validation/",
            'C2BconfirmationURL' => "https://192.168.0.9:8310/C2B/confirmation/",
            'C2Bshortcode' => "555676",
            'c2BRegisterEndPointUrl' => "http://196.201.214.137:8310/mminterface/registerURL",
            'C2Bpassword' => 'Kenya123!@',
            'C2BServiceID' => '107015000',
            'C2BDebitAccount' => '######',
            /* End of C2B Configurations */

            /* B2B Configurations */
            'B2Bshortcode' => '#####',
            'B2Binitiator' => '#####',
            'B2BinitiatorPassword' => '#####',
            'B2BresultUrl' => 'https://192.168.0.9:8310/result/B2B/',
            /* End of B2B Configurations */

            /* Jpos XML listner server */
            'localXMLISOServerIP' => 'localhost',
            'localXMLISOServerPort' => 8001,
            /* ETOPUP */
            'etopUpEndPointURL' => 'http://196.201.214.53:5660/?VENDOR=D-M173&REQTYPE=EXRCTRFREQ&DATA=',
            //'etopUpEndPointURL' => 'http://196.201.214.53:5660',
            'etopUpMSISDN' => '####',
            'etopUpPin' => '####',
            'etopUpDealerCode' => '####',
            'etopUpPassword' => '####',
        );


        if (isset($configurations[$config])) {
            return $configurations[$config];
        } elseif (strlen(trim($defaultIfNotExist)) > 0) {
            Utils::logThis("INFO", "config $config not found, returning default $defaultIfNotExist");
            return $defaultIfNotExist;
        } else {
            Utils::logThis("ERROR", "requested Config $config not exist");
            return false;
        }
    }

}
