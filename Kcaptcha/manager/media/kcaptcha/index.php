<?php

error_reporting (E_ALL);

include('kcaptcha.php');

session_start();

$captcha = new KCAPTCHA();
$_SESSION['veriword'] = $captcha->getKeyString();

?>