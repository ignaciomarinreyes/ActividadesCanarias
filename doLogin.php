<?php
include_once 'business.class.php';
if (count($_POST) == 0 ||
    !isset($_POST['username']) ||
    !isset($_POST['password']) ||
    !UserLogged::login($_POST['username'], $_POST['password'])) {
    UserDataFromPost::save();
} else {
    UserDataFromPost::clean();
}
header("Location: " . Script::getRequestUri());