<?php
session_start();
session_unset();
session_destroy();
setcookie("karigar_id", "", time() - 3600, "/");
setcookie("karigar_name", "", time() - 3600, "/");
header("Location: index.php");
exit;
