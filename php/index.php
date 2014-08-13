<?php
/**
 * User: ibrahimaltinoluk
 * Date: 5.08.2014
 * Time: 00:42
 */

require_once 'config/parameters.php';
require_once 'config/db_connection.php';
require_once 'app.php';


$service = $app->create("Service");


$service->apply();

