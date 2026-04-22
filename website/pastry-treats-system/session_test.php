<?php
session_start();
$_SESSION['test'] = "Session is working!";
echo "Session test set. Reload this page to check!";
?>
