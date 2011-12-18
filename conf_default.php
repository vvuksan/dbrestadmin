<?php


$conf['db_hostname'] = 'localhost';
$conf['db_user'] = 'mysqlrest';
$conf['db_pass'] = 'mysqlrest';
$conf['db_name'] = 'mysql';

$conf['dsn'] = "mysql://" . $conf['db_user'] . ":" . $conf['db_pass'] . "@" .$conf['db_hostname'] . "/" . $conf['db_name'];

// Turn on Silex debug
$conf['debug'] = false;

?>
