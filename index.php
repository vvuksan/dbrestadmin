<?php

require_once __DIR__.'/silex.phar';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app['debug'] = true;

// Load configs
require_once __DIR__. "/conf_default.php";

# Include user-defined overrides if they exist.
if( file_exists( __DIR__ . "/conf.php" ) ) {
  include_once __DIR__ . "/conf.php";
}


/////////////////////////////////////////////////////////////////////////////
// Display available databases
/////////////////////////////////////////////////////////////////////////////
$app->get('/databases', function (Silex\Application $app, Request $request) use ($conf) {
    $output = '';

    require_once 'MDB2.php';

    $mdb2 =& MDB2::factory($conf['dsn']);
    $mdb2->setOption('debug', $app['debug']);
    if (PEAR::isError($mdb2)) {
      die("I can't connect to the database. Notifying the administrators. Sorry for the inconvenience");
    }
    
    $statement = $mdb2->prepare('SHOW DATABASES');
    $res = $statement->execute();
    $statement->free();
    
    if ( $res->numRows() > 0 ) {
      while (($row = $res->fetchRow())) {
        $dbs[] = $row[0];
      }
    } else {
      die("No databases found");
    }
  
    $res->free();

    
    return json_encode($dbs);
});

$app->post('/database', function (Silex\Application $app, Request $request) use ($conf) {

    $dbname =   $request->get('dbname');
    
    if ( $dbname != NULL ) {

        require_once 'MDB2.php';
    
        $mdb2 =& MDB2::factory($conf['dsn']);
        $mdb2->setOption('debug', $app['debug']);
        if (PEAR::isError($mdb2)) {
          error_log("I can't connect to the database. Notifying the administrators. Sorry for the inconvenience");
        }
        
        $res =& $mdb2->query('CREATE DATABASE ' . $dbname);
        if (PEAR::isError($res)) {
            return $res->getMessage();
        }
    
        return "OK";
    
    } else {
        return "Invalid request. No DB name (dbname) specified.";
    }
    
});


$app->run();

?>