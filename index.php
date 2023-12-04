<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
mb_internal_encoding("UTF-8");
require("constants.php");
require("baseFunctions.php");

set_error_handler("exceptionsErrorHandler");
spl_autoload_register("autoloader");

try { Dtb::connect(HOST, DATABASE, USER, PSWD); }
catch (\Exception $ex)
{
    logException($ex);
    echo("Server cannot process your request due to an internal error.");
    exit();
}

$router = new RouterController();
$router->process(array($_SERVER["REQUEST_URI"]));
$router->renderView();
