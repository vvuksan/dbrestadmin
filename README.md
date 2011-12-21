# DB Rest Admin

dbrestadmin is a project to provide a RESTful interface to DB admin functions. I use
it for mySQL however it should work for other databases as well.
My primary use case is the ability to create databases and users in a dev environment
where we may be deploying test versions. 

## Usage

To get a list of database servers use GET request against

```
http://myhost/dbrestadmin/databases
```

You will get a list of servers as resources. Follow it to figure out what you can do.

To create a database you just POST dbname to /databases/ e.g. to create testdb

```
curl -X POST http://myhost/dbrestadmin/databases/0/dbs/testdb
```
Databases can only contain alphanumeric characters and a dash.

To create a user

```
curl -X POST "http://localhost:8000/dbrestadmin/databases/0/users/test2@localhost" -d "password=test"
```

This translates to

CREATE USER test2@localhost IDENTIFIED BY 'test'

To add grants to a user

curl -X POST "http://localhost:8000/dbrestadmin/databases/0/users/test2@'localhost'/grants" -d "grants=all privileges&database=testdb"

This translates to

GRANT ALL PRIVILEGES ON testdb.* TO test2@'localhost'



### Prerequisites

    PHP 5.3+
    PHP PEAR
    MDB2 PEAR library

## Installation

Install PHP Pear ie. on Ubuntu

apt-get install php-pear

Install MDB2 pear library and the appropriate driver for the database you want to
manage e.g.

```
pear install MDB2
pear install MDB2#mysql
```

Copy the contents of the repo into your webroot. If it doesn't work make sure you have AllowOverride
allow .htaccess. If you want to be lazy just add this.

```
<Directory /var/www/dbrestadmin>
AllowOverride All
</Directory>
```

## Configuration

To configure create a file in the webroot called conf.php which overrides values from conf_default.php.
At a minimum you will need to define at least one server to manage. All you need to do at this time
is add it to $conf['servers'] array with name, type and dsn attribute.

```
$conf['servers'][] = array (
    "name" => "Localhost",
    "type" => "mysql",
    "dsn" => "mysql://mysqlrest:mysqlrest@127.0.0.1/mysql"
);
```


## Security

Security of the REST API is not that great at this point. You may want at a minimum
use basic auth to access the API. Even with basic auth some of the functions like user
creation do not have proper input validation/sanitization so beware. I will add those
over time.