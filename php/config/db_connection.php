<?php
/**
 * Created by PhpStorm.
 * User: ibrahimaltinoluk
 * Date: 6.08.2014
 * Time: 23:25
 */
error_reporting(E_ERROR   );

mysql_connect(DB_ADDRESS, DB_USER_NAME,DB_USER_PASS);
mysql_select_db(DB_NAME);
mysql_query("SET NAMES utf8");

