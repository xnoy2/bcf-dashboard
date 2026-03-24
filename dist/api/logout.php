<?php
session_start();
session_destroy();
header("Location: /../dist/auth/login.php");