<?php

// exceptions handling
function exceptionsErrorHandler(int $severity, string $message, string $filename, int $lineno): bool
{
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}

function logException(\Exception $ex, string $pathToErrlogile = null)
{
    $record = $ex->getMessage();
    $record .= PHP_EOL . "file: " . $ex->getFile();
    $record .= PHP_EOL . "line: " . $ex->getLine();
    if ($pathToErrlogile === null)
        error_log($record . PHP_EOL . PHP_EOL, 3, ERRLOG_FILE);
    else
        error_log($record . PHP_EOL . PHP_EOL, 3, $pathToErrlogile);
}

// autoloader
function autoloader(string $class): void
{
    if (preg_match('/Controller$/', $class))
        require("controllers/" . $class . ".php");
    else
        require("models/" . $class . ".php");
}
