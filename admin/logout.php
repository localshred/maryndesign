<?php
session_destroy();
header("Location: /admin/login.php?logout=1");
?>