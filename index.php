<?php

require_once __DIR__.'/silex.phar';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

// Load configs
require_once __DIR__. "/conf_default.php";

# Include user-defined overrides if they exist.
if( file_exists( __DIR__ . "/conf.php" ) ) {
  include_once __DIR__ . "/conf.php";
}

function called_url() {
  $pageURL = 'http';
  if ( isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
  $pageURL .= "://";
  if ($_SERVER["SERVER_PORT"] != "80") {
   $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
  } else {
   $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
  }
  return $pageURL;
}

// Should be false if in production
$app['debug'] = $conf['debug'];

/////////////////////////////////////////////////////////////////////////////
// Welcome page
/////////////////////////////////////////////////////////////////////////////
$app->get('/', function () {
  
  return "This looks promising.";

});

/////////////////////////////////////////////////////////////////////////////
// Display available databases
/////////////////////////////////////////////////////////////////////////////
$app->get('/databases', function (Silex\Application $app, Request $request) use ($conf) {

  $servers = array();

  // Iterate through the list of configured servers
  foreach ( $conf['servers'] as $index => $server ) {
    $servers[] = array( "href" => called_url() . "/" . $index  ,"name" => $server['name'], "type" => $server['type'] );
  }
  $response = new Response(json_encode($servers), 200);
  $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

  return $response;

});

/////////////////////////////////////////////////////////////////////////////
// Display available databases on a server
/////////////////////////////////////////////////////////////////////////////
$app->get('/databases/{id}', function (Silex\Application $app, Request $request, $id) use ($conf) {

    require_once 'MDB2.php';

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }
    
    $mdb2 =& MDB2::factory($dsn);
    $mdb2->setOption('debug', $conf['debug']);
    if (PEAR::isError($mdb2)) {
      return new Response('Database is not accessible', 503);
    }
    
    // Get a list of databases
    $statement = $mdb2->prepare('SHOW DATABASES');
    $res = $statement->execute();
    $statement->free();
    
    // What's the base URL
    $base_url = called_url();
    
    if ( $res->numRows() > 0 ) {
      while (($row = $res->fetchRow())) {
        $dbs[] = array( "href" => $base_url . "/" . $row[0] , "dbname" => $row[0] );
      }
    } else {
      die("No databases found");
    }
  
    $res->free();

    $response = new Response(json_encode($dbs), 200);
    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
    
    // TODO: Need to output this as resources
    return $response;
});

/////////////////////////////////////////////////////////////////////////////
// Create a database on a particular server
/////////////////////////////////////////////////////////////////////////////
$app->post('/databases/{id}/{dbname}', function (Silex\Application $app, Request $request, $id, $dbname) use ($conf) {
    
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

        $response = new Response("Database created", 201);
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

        return $response;
    
    } else {
        return new Response('Invalid request. No DB name (dbname) specified.', 400);
    }
    
});

/////////////////////////////////////////////////////////////////////////////
// Drop a database on a particular server
/////////////////////////////////////////////////////////////////////////////
$app->delete('/databases/{id}/{dbname}', function (Silex\Application $app, Request $request, $id, $dbname) use ($conf) {

    if ( $dbname != NULL ) {

        require_once 'MDB2.php';
    
        $mdb2 =& MDB2::factory($conf['dsn']);
        $mdb2->setOption('debug', $app['debug']);
        if (PEAR::isError($mdb2)) {
          error_log("I can't connect to the database. Notifying the administrators. Sorry for the inconvenience");
        }
        
        $res =& $mdb2->query('DROP DATABASE ' . $dbname);
        if (PEAR::isError($res)) {
            return new Response("Delete failed due to " . $res->getMessage(), 400);
        }
    
        $response = new Response("Resource deleted successfully", 200);
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
    
        return $response;
    
    } else {
        return new Response('Invalid request. No DB name (dbname) specified.', 400);
    }
    
});

$app->run();

?>