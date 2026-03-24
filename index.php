<?php

session_start();
if(isset($_SESSION['use'])) {
  header("Location: main/dashboard.php");
} else {
  header("Location: auth/login.php");
}

?>