# DB Rest Admin

dbrestadmin is a project to provide a RESTful interface to DB admin functions. I use
it for mySQL however it should work for other databases as well.
My primary use case is the ability to create databases and users in a dev environment
where we may be deploying test versions. 

## Usage

To get a list of database servers use GET request against

http://myhost/dbrestadmin/databases

You will get a list of servers as resources

To create a database you just POST dbname to /databases/ e.g. to create testdb

curl -X POST http://myhost/dbrestadmin/databases/0/db/testdb

### Prerequisites

PHP 5.3+
PHP PEAR
MDB2 PEAR library

## Installation

Install PHP Pear ie. on Ubuntu

apt-get install php-pear

Install MDB2 pear library and the appropriate driver for the database you want to
manage e.g.

pear install MDB2
pear install MDB2#mysql

Copy the contents of the repo into your webroot. If it doesn't work make sure you have AllowOverride
allow .htaccess. If you want to be lazy just add this.

```
<Directory /var/www/dbrestadmin>
AllowOverride All
</Directory>
```

## Configuration

To configure create a file in the webroot called conf.php which overrides values from conf_default.php.
At this point I would copy all the contents from conf_default and change them to your liking.
mySQLrestadmin user needs to have CREATE database privileges.

## Security

Security of the REST API is up to you at this point. I use basic auth to access the API.