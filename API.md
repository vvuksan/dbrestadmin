# API reference

## Databases

Databases represents all database (servers) dbrestadmin knows of. These are
defined in conf.php

### List Resource


```
/dbrestadmin/v1/databases
```


### GET

Returns a list of all defined database servers (identified with different names and types)

```
[
  {
    "href": "http:\/\/localhost:8000\/v1/dbrestadmin\/databases\/0",
    "name": "Localhost",
    "type": "mysql"
  },
  {
    "href": "http:\/\/localhost:8000\/v1/dbrestadmin\/databases\/1",
    "name": "engsw-irva-09.broadcom.com",
    "type": "mysql"
  }
]
```

### Instance Resource

```
/dbrestadmin/v1/databases/{db_id}
```

Database instance resource represents a single database server. Returns list of
resources that can be managed. Currently those are dbs (different database names)
and users.

### GET

```
GET /dbrestadmin/v1/databases/{db_id} HTTP/1.1
```

```
[
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/databases\/0\/dbs",
    "name": "dbs"
  },
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/databases\/0\/users",
    "name": "users"
  }
]
```

### Instance Resource

```
/dbrestadmin/v1/databases/{db_id}/dbs
```

Represents databases (dbnames) on a single database server.

### GET

```
GET /dbrestadmin/v1/databases/{db_id}/dbs HTTP/1.1
```

Returns a list of databases on the database server

```
[
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/databases\/0\/dbs\/information_schema",
    "dbname": "information_schema"
  },
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/databases\/0\/dbs\/mysql",
    "dbname": "mysql"
  },
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/databases\/0\/dbs\/tattle",
    "dbname": "tattle"
  }
]
```

### Instance Resource

```
/dbrestadmin/v1/databases/{db_id}/dbs/{db_name}
```

### GET

Not supported

### POST

```
POST /dbrestadmin/v1/databases/{db_id}/dbs/{db_name} HTTP/1.1
```

```
Database created
```

Creates a database with {db_name} on database server. Database name can contain
only alphanumeric characters and dashes e.g. needs to match this regex
^[a-zA-Z0-9-]*$. API will return a HTTP 201 if DB has been created,
400 if DB name is not valid.



### POST

```
DELETE /dbrestadmin/v1/databases/{db_id}/dbs/{db_name} HTTP/1.1
```

```
Resource deleted successfully
```

Deletes the database

### Instance Resource

```
/dbrestadmin/v1/databases/{db_id}/users
```

Represents users on a single database server.

### GET

```
GET /dbrestadmin/v1/databases/{db_id}/users HTTP/1.1
```

Returns a list of users on a database server


```
[
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/v1\/databases\/0\/users\/root@'127.0.0.1'",
    "user": "root",
    "host": "127.0.0.1",
    "userathost": "root@'127.0.0.1'"
  },
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/v1\/databases\/0\/users\/mysqlrest@'localhost'",
    "user": "mysqlrest",
    "host": "localhost",
    "userathost": "mysqlrest@'localhost'"
  },
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/v1\/databases\/0\/users\/root@'localhost'",
    "user": "root",
    "host": "localhost",
    "userathost": "root@'localhost'"
  }
]
```

### Instance Resource

```
/dbrestadmin/v1/databases/{db_id}/users/{userid}
```

### GET

Returns resources available for user

```
GET /dbrestadmin/v1/databases/{db_id}/users/{userid} HTTP/1.1
```

Currently grants and dbprivs are available

```
[
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/v1\/databases\/0\/users\/root@%27127.0.0.1%27\/grants",
    "name": "grants"
  },
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/v1\/databases\/0\/users\/root@%27127.0.0.1%27\/dbprivs",
    "name": "dbprivs"
  }
]
```

### POST

Parameters

password = specifies users password

```
POST /dbrestadmin/v1/databases/{db_id}/users/{userid} 
```

Creates a user with userid. Userid is of the form userathost for mySQL, password
needs to be supplied. e.g.

```
POST /dbrestadmin/v1/databases/0/users/test@localhost password=secret
```

### DELETE

```
DELETE /dbrestadmin/v1/databases/{db_id}/users/{userid} 
```

Deletes a user with userid. Userid is of the form userathost for mySQL.


### Instance Resource

Grants given to a particular user

```
/dbrestadmin/v1/databases/{db_id}/users/{userid}/grants
```

### GET

```
GET /dbrestadmin/v1/databases/{db_id}/users/{userid}/grants HTTP/1.1
```

Returns a list of grants given to a user

```
{
  "grants": [
    "GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION"
  ]
}
```

### POST

Parameters

grants = comma separated list of grants
dbname = database name for the grants

```
POST /dbrestadmin/v1/databases/{db_id}/users/{userid}/grants grants=select,insert&dbname=test
```

### Instance Resource

Shows databases on which user is assigned privileges

```
/dbrestadmin/v1/databases/{db_id}/users/{userid}/dbprivs
```

### GET

```
GET /dbrestadmin/v1/databases/{db_id}/users/{userid}/dbprivs HTTP/1.1
```

```
[
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/v1\/databases\/0\/users\/tattle@%27localhost%27\/dbprivs\/tattle",
    "dbname": "tattle"
  }
]
```

### POST

Currently not implemented


### Instance Resource

Shows user's privileges on a particular database.

```
/dbrestadmin/v1/databases/{db_id}/users/{userid}/dbprivs/{dbname}
```

### GET

```
GET /dbrestadmin/v1/databases/{db_id}/users/{userid}/dbprivs/{dbname} HTTP/1.1
```

```
[
  {
    "priv": "select",
    "value": "Y"
  },
  {
    "priv": "insert",
    "value": "Y"
  },
  {
    "priv": "update",
    "value": "Y"
  },
  {
    "priv": "delete",
    "value": "Y"
  },
  {
    "priv": "create",
    "value": "Y"
  },
  {
    "priv": "drop",
    "value": "Y"
  },
  {
    "priv": "grant",
    "value": "N"
  },
...
```

### POST

Not implemented