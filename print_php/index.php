<?php

require_once "src/PrintData.php";

$print = new PrintData();
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
    case 'GET':
        $print->get_data();
        break;
    case 'POST':
        $print->post_data();
        break;
    default:
        // Invalid Request Method
        header("HTTP/1.0 405 Method Not Allowed");
        break;
        break;
}
