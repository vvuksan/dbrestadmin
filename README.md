# mySQL Rest Admin

mysqladminrest is a project to provide a RESTful interface to mySQL admin functions. 
My primary use case is the ability to create databases and users for a dev environment.

## Usage

To get a list of databases use GET request against

http://myhost/mysqlrestadmin/databases

To create a database you just POST dbname to /database e.g.

curl -X POST http://myhost/mysqlrestadmin/database  -d "dbname=test" 

## Configuration

To configure create a file in the webroot called conf.php which overrides values from conf_default.php.
At this point I would copy all the contents from conf_default and change them to your liking.
mySQLrestadmin user needs to have CREATE database privileges.

## Limitations

Currently only one mySQL server is supported. I intend to lift that limit hopefully shortly.
