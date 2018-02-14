<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$route['default_controller']    = 'login/login';
$route['404_override']          = '';
$route['translate_uri_dashes']  = FALSE;

// Mpesa
$route["B2CCallback"]           = 'callbacks/processB2CRequestCallback';
$route["B2BCallback"]           = 'callbacks/processB2BRequestCallback';
$route["C2BValidation"]         = 'callbacks/processC2BRequestValidation';
$route["C2BConfirmation"]       = 'callbacks/processC2BRequestConfirmation';
$route["AccountBalCallback"]    = 'callbacks/processAccountBalanceRequestCallback';
$route["ReversalCallback"]      = 'callbacks/processReversalRequestCallBack';
$route["RequestStkCallback"]    = 'callbacks/processSTKPushRequestCallback';
$route["QueryStkCallback"]      = 'callbacks/processSTKPushQueryRequestCallback';
$route["TransStatCallback"]     = 'callbacks/processTransactionStatusRequestCallback';

$route["forgotpassword"]		= 'login/forgotPass';
$route["changepassword/(:any)"] = 'login/changePass/$1';
$route["login"]                 = 'login/login';
$route["logout"]                = 'login/logout';

