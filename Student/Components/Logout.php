<?php
session_start();
session_destroy();
header("Location: /Public/Components/Login.php");
exit;
?>