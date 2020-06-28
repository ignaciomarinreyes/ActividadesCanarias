<?php
include_once 'business.class.php';
$response = new stdClass();
$response->saved = false;
$response->error = '';
$response->errors = new ArrayObject();
if (Ticket::checkDataFromPost($response->errors)) {
    if (!Ticket::saveNew($_POST['units'])) {
        $response->error = 'Error inesperado';
    } else {
        $response->saved = true;
    }
}
header('Content-type: application/json');
echo json_encode($response);