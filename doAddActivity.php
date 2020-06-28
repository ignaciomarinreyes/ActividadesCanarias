<?php
include_once 'business.class.php';
$response = new stdClass();
$response->saved = false;
$response->error = '';
$response->errors = new ArrayObject();
if (Activity::checkDataFromPost(true, $response->errors)) {
    if (!Activity::saveNew($_POST['name'], $_POST['type'],
        $_POST['description'], $_POST['price'], $_POST['capacity'],
        $_POST['startDate'], $_POST['duration'], $_FILES['image'])) {
        $response->error = 'Error inesperado';
    } else {
        $response->saved = true;
    }
}
header('Content-type: application/json');
echo json_encode($response);