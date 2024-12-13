<?php

function log($message)
{
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/services/log.txt", date("Y/m/d | H:i:s") . " | " . $message . "\n", FILE_APPEND);
}

log("hello");

log("world");
