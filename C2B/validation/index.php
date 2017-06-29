<?php

include "../../Classes/Utils.php";
include "../../Classes/Config.php";

$request = file_get_contents("php://input");

Utils::logThis("INFO", __METHOD__ . "|received request " . print_r($request, true));

Utils::processC2BValidation($request);



