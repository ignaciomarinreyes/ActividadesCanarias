<?php
include_once 'business.class.php';
UserLogged::logout();
header("Location: " . Script::getRequestUri());