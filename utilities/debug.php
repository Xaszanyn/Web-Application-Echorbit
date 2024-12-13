<?php

function log_text($message)
{
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/services/log.txt", date("Y/m/d | H:i:s") . " | " . $message . "\n", FILE_APPEND);
}

log_text("hello");

log_text("world");
