<?php
require_once '/var/www/html/src/config/config.php';
require_once '/var/www/html/src/config/Database.php';
require_once '/var/www/html/src/config/DBInitializer.php';

$init = new DBInitializer();
$init->init();
