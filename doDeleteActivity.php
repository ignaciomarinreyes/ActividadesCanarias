<?php
include_once 'business.class.php';
$response = new stdClass();
$response->deleted = false;
$response->error = '';
if (count($_POST) == 0 ||
    !isset($_POST['id']) ||
    !Activity::delete($_POST['id'])) {
    $response->error = 'Error al eliminar la actividad';
} else {
    $response->deleted = true;
}
header('Content-type: application/json');
echo json_encode($response);