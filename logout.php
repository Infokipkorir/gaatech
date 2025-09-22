<?php
session_start();
session_destroy();
header('Location: entry_page.php');
exit;
