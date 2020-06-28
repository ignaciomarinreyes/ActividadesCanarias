<?php
include_once 'presentation.class.php';
View::showActivityTable(Activity::getSearchedActivities($_POST['valueTextArea']));