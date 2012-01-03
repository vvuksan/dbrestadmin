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

/////////////////////////////////////////////////////////////////////////////
// Returns currently called URL
/////////////////////////////////////////////////////////////////////////////
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

$api_ver = "/v1";

/////////////////////////////////////////////////////////////////////////////
// Start page
/////////////////////////////////////////////////////////////////////////////
$app->get($api_ver . '/', function () {

    require __DIR__ . "/tools.php";
    // Resources we support
    $resource_list = array("databases");

    // What's the base URL
    $base_url = called_url();
    
    foreach ( $resource_list as $index => $resource ) {
      $resources[] = array( "href" => $base_url . "/" . $resource, "name" => $resource );
    }
    
    $response = new Response(json_prettyprint(json_encode($resources)), 200);
    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');    

    return $response;

});

/////////////////////////////////////////////////////////////////////////////
// Display available databases
/////////////////////////////////////////////////////////////////////////////
$app->get($api_ver . '/databases', function (Silex\Application $app, Request $request) use ($conf) {

  require __DIR__ . "/tools.php";

  $servers = array();

  // Iterate through the list of configured servers
  foreach ( $conf['servers'] as $index => $server ) {
    $servers[] = array( "href" => called_url() . "/" . $index  ,"name" => $server['name'], "type" => $server['type'] );
  }
  $response = new Response(json_prettyprint(json_encode($servers)), 200);
  $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

  return $response;

});

/////////////////////////////////////////////////////////////////////////////
// Display available resources on the database server
/////////////////////////////////////////////////////////////////////////////
$app->get($api_ver . '/databases/{id}', function (Silex\Application $app, Request $request, $id) use ($conf) {

    require_once 'MDB2.php';
    require __DIR__ . "/tools.php";

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }

    // Resources we support
    $resource_list = array("dbs", "users");

    // What's the base URL
    $base_url = called_url();
    
    foreach ( $resource_list as $index => $resource ) {
      $resources[] = array( "href" => $base_url . "/" . $resource,
        "name" => $resource);
    }
    
    $response = new Response(json_prettyprint(json_encode($resources)), 200);
    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');    

    return $response;

});


/////////////////////////////////////////////////////////////////////////////
// Display available databases on a server
/////////////////////////////////////////////////////////////////////////////
$app->get($api_ver . '/databases/{id}/dbs', function (Silex\Application $app, Request $request, $id) use ($conf) {

    require_once 'MDB2.php';
    require __DIR__ . "/tools.php";

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }
    
    $mdb2 =& MDB2::connect($dsn);
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
        $dbs[] = array( "href" => $base_url . "/db/" . $row[0] , "dbname" => $row[0] );
      }
    } else {
      die("No databases found");
    }
  
    $res->free();

    $response = new Response(json_prettyprint(json_encode($dbs)), 200);
    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
    
    // TODO: Need to output this as resources
    return $response;
});

/////////////////////////////////////////////////////////////////////////////
// Display available databases on a server
/////////////////////////////////////////////////////////////////////////////
$app->get($api_ver . '/databases/{id}/dbs', function (Silex\Application $app, Request $request, $id) use ($conf) {

    require_once 'MDB2.php';
    require __DIR__ . "/tools.php";

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }
    
    $mdb2 =& MDB2::connect($dsn);
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

    $response = new Response(json_prettyprint(json_encode($dbs)), 200);
    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
    
    return $response;
});


/////////////////////////////////////////////////////////////////////////////
// Create a database on a particular server
/////////////////////////////////////////////////////////////////////////////
$app->post($api_ver . '/databases/{id}/dbs/{dbname}', function (Silex\Application $app, Request $request, $id, $dbname) use ($conf) {
    
    // Make sure dbname contains only alphanumeric characters
    if ( preg_match('/^[a-zA-Z0-9-]*$/', $dbname) ) {

        require_once 'MDB2.php';
    
        $mdb2 =& MDB2::connect($conf['dsn']);
        $mdb2->setOption('debug', $app['debug']);
        if (PEAR::isError($mdb2)) {
          return new Response("I can't connect to the database. Notifying the administrators. Sorry for the inconvenience", 500);
        }
        
        
        # TODO validate dbname
        
        $res =& $mdb2->query('CREATE DATABASE ' . $dbname);
        if (PEAR::isError($res)) {
            return new Response("Create failed due to " . $res->getMessage(), 400);
        }

        $response = new Response("Database created", 201);
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');

        return $response;
    
    } else {
        return new Response('Invalid request. DB name can only contain alpha numeric characters and a dash.', 400);
    }
    
});

/////////////////////////////////////////////////////////////////////////////
// Drop a database on a particular server
/////////////////////////////////////////////////////////////////////////////
$app->delete($api_ver . '/databases/{id}/dbs/{dbname}', function (Silex\Application $app, Request $request, $id, $dbname) use ($conf) {

    // Make sure dbname contains only alphanumeric characters
    if ( preg_match('/^[a-zA-Z0-9-]*$/', $dbname) ) {

        require_once 'MDB2.php';
    
        $mdb2 =& MDB2::connect($conf['dsn']);
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

/////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
// USERS
/////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
// Display available users on a particular server
/////////////////////////////////////////////////////////////////////////////
$app->get($api_ver . '/databases/{id}/users', function (Silex\Application $app, Request $request, $id) use ($conf) {

    require_once 'MDB2.php';
    require __DIR__ . "/tools.php";

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }
    
    $mdb2 =& MDB2::connect($dsn);
    if (PEAR::isError($mdb2)) {
      return new Response('Database is not accessible', 503);
    }
    
    // Get a list of databases
    $statement = $mdb2->prepare('SELECT host, user FROM mysql.user;');
    $res = $statement->execute();
    $statement->free();
    
    // What's the base URL
    $base_url = called_url();
    
    $users = array();
    
    if ( $res->numRows() > 0 ) {
      while (($row = $res->fetchRow())) {
        // Join the username 
        $userathost = $row[1] . "@" . "'" . $row[0] . "'";
        $users[] = array( "href" => $base_url . "/" . $userathost ,
              "user" => $row[1],
              "host" => $row[0],
              "userathost" => $userathost );
      }
    }
    
    $res->free();

    $response = new Response(json_prettyprint(json_encode($users)), 200);
    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
    
    return $response;
});

/////////////////////////////////////////////////////////////////////////////
// Create a user on a database server
/////////////////////////////////////////////////////////////////////////////
$app->post($api_ver . '/databases/{id}/users/{user}', function (Silex\Application $app, Request $request, $id, $user) use ($conf) {

    require_once 'MDB2.php';

    $password = $request->get('password');
    
    if ( $password === NULL )
      return new Response('Password needs to be specified', 503);

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }
    
    $mdb2 =& MDB2::connect($dsn);
    if (PEAR::isError($mdb2)) {
      return new Response('Database is not accessible', 503);
    }

    // TODO validate user
    $sql = "CREATE USER " . $user . " IDENTIFIED BY '" . $password  . "'";
    $res =& $mdb2->queryAll($sql);
    if (PEAR::isError($res)) {
      return new Response('User could not be created', 503);
    }

    return new Response('User created', 201);

});

/////////////////////////////////////////////////////////////////////////////
// Drop user from database server
/////////////////////////////////////////////////////////////////////////////
$app->delete($api_ver . '/databases/{id}/users/{user}', function (Silex\Application $app, Request $request, $id, $user) use ($conf) {

    require_once 'MDB2.php';

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }
    
    $mdb2 =& MDB2::connect($dsn);
    if (PEAR::isError($mdb2)) {
      return new Response('Database is not accessible', 503);
    }

    // TODO validate sanitize user
    $sql = "DROP USER " . $user;
    $res =& $mdb2->queryAll($sql);
    if (PEAR::isError($res)) {
      return new Response('User could not be created', 503);
    }

    return new Response('User deleted', 201);

});

/////////////////////////////////////////////////////////////////////////////
// Display available resources for user
/////////////////////////////////////////////////////////////////////////////
$app->get($api_ver . '/databases/{id}/users/{user}', function (Silex\Application $app, Request $request, $id, $user) use ($conf) {

    require __DIR__ . "/tools.php";

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }

    // Resources we support
    $resource_list = array("grants", "dbprivs");

    // What's the base URL
    $base_url = called_url();
    
    foreach ( $resource_list as $index => $resource ) {
      $resources[] = array( "href" => $base_url . "/" . $resource, "name" => $resource );
    }
    
    $response = new Response(json_prettyprint(json_encode($resources)), 200);
    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');    

    return $response;
    

});

/////////////////////////////////////////////////////////////////////////////
// Display all grants for user
/////////////////////////////////////////////////////////////////////////////
$app->get($api_ver . '/databases/{id}/users/{user}/grants', function (Silex\Application $app, Request $request, $id, $user) use ($conf) {

    require_once 'MDB2.php';
    require __DIR__ . "/tools.php";

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }
    
    $mdb2 =& MDB2::connect($dsn);
    $mdb2->setOption('debug', $conf['debug']);
    if (PEAR::isError($mdb2)) {
      return new Response('Database is not accessible', 503);
    }
    
    // Get a list of databases
    // TODO figure out a way to validate/sanitize USER
    $sql = "SHOW GRANTS FOR " . $user . "";
    $res =& $mdb2->queryAll($sql);
    if (PEAR::isError($res)) {
      return $res->getMessage();
    }

    foreach ($res as $index => $grant ) {
      $grants['grants'][] = $grant[0];
    }
  
    $response = new Response(json_prettyprint(json_encode($grants)), 200);
    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
    
    return $response;
});


/////////////////////////////////////////////////////////////////////////////
// Add grants for user
/////////////////////////////////////////////////////////////////////////////
$app->post($api_ver . '/databases/{id}/users/{user}/grants', function (Silex\Application $app, Request $request, $id, $user) use ($conf) {

    require_once 'MDB2.php';
    require __DIR__ . "/tools.php";

    $grants = $request->get('grants');
    $database = $request->get('dbname');
    
    if ( $grants === NULL )
      return new Response('You need to specify grants', 503);

    // Make sure DB name has been specified and that dbname is proper ie. alphanumeric
    if ( $database === NULL || ! preg_match('/^[a-zA-Z0-9-]*$/', $database) )
      return new Response('You need to specify a database or DB name is invalid', 503);

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }
    
    $mdb2 =& MDB2::connect($dsn);
    if (PEAR::isError($mdb2)) {
      return new Response('Database is not accessible', 503);
    }

    // TODO validate user
    $sql = "GRANT " . $grants . " ON " . $database . ".* TO " . $user;
    $res =& $mdb2->queryAll($sql);
    if (PEAR::isError($res)) {
      return new Response('Grants could not be added. Message' . $res->getMessage(), 503);
    }

    return new Response('Grants added', 201);

});



/////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
// Display db privileges
/////////////////////////////////////////////////////////////////////////////
$app->get($api_ver . '/databases/{id}/users/{user}/dbprivs', function (Silex\Application $app, Request $request, $id, $user) use ($conf) {

    require_once 'MDB2.php';
    require __DIR__ . "/tools.php";

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }
    
    $mdb2 =& MDB2::connect($dsn);
    $mdb2->setOption('debug', $conf['debug']);
    if (PEAR::isError($mdb2)) {
      return new Response('Database is not accessible', 503);
    }
    
    // Get a list of databases
    $statement = $mdb2->prepare('SELECT Db from db WHERE User = ? and Host = ?');
    // We might get ' in the username. Strip them out
    $data = explode("@", str_replace("'", "", $user));
    $res = $statement->execute($data);
    $statement->free();
    
    // What's the base URL
    $base_url = called_url();
    
    $dbs = array();
    
    if ( $res->numRows() > 0 ) {
      while (($row = $res->fetchRow())) {
        $dbs[] = array( "href" => $base_url . "/" . $row[0] ,
          "dbname" => $row[0]
        );
      }
    }
  
    $response = new Response(json_prettyprint(json_encode($dbs)), 200);
    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
    
    return $response;
});

/////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////
// Display db privileges
/////////////////////////////////////////////////////////////////////////////
$app->get($api_ver . '/databases/{id}/users/{user}/dbprivs/{dbname}', function (Silex\Application $app, Request $request, $id, $user, $dbname) use ($conf) {

    require_once 'MDB2.php';
    require __DIR__ . "/tools.php";

    // Check that the server exists and has a dsn defined
    if ( isset($conf['servers'][$id]['dsn']) ) {
      $dsn = $conf['servers'][$id]['dsn'];
    } else {
      return new Response('Database Server ID not found or DSN not defined', 404);
    }
    
    $mdb2 =& MDB2::connect($dsn);
    $mdb2->setOption('debug', $conf['debug']);
    if (PEAR::isError($mdb2)) {
      return new Response('Database is not accessible', 503);
    }
    
    // Get a list of databases
    $statement = $mdb2->prepare('SELECT * from db WHERE User = ? and Host = ? and Db = ?');
    // We might get ' in the username. Strip them out
    $data = explode("@", str_replace("'", "", $user));
    $data[] = $dbname;
    $res = $statement->execute($data);
    $statement->free();
    
    // What's the base URL
    $base_url = called_url();
    
    $grants = array();
    
    $col_names = $res->getColumnnames();
    
    if ( $res->numRows() > 0 ) {
      while (($row = $res->fetchRow())) {
        foreach ( $col_names as $priv => $index ) {
          if ( preg_match("/_priv/", $priv) ) {
            $privs[] = array( "priv" => str_replace("_priv", "", $priv),
              "value" => $row[$index]
            );            
          }
        }
      }
    }
  
    $response = new Response(json_prettyprint(json_encode($privs)), 200);
    $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
    
    return $response;
});


// GO GO GO
$app->run();

?>