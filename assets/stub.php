<?php

if (empty($this)) {
    $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.0";
    header("$protocol 404 Not Found");
    die;
}
