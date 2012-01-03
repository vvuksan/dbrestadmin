# API reference

## Databases

Databases represents all database (servers) defined

### List Resource

```/dbrestadmin/databases```

### GET

Returns a list of all defined database servers (identified with different names and types)

```
[
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/databases\/0",
    "name": "Localhost",
    "type": "mysql"
  },
  {
    "href": "http:\/\/localhost:8000\/dbrestadmin\/databases\/1",
    "name": "engsw-irva-09.broadcom.com",
    "type": "mysql"
  }
]
```

### Instance Resource

```/dbrestadmin/databases/{db_id}```

Database instance resource represents a single database server. Returns list of
resources that can be managed. Currently those are dbs (different database names)
and users.

### GET

```GET /dbrestadmin/databases/{db_id} HTTP/1.1```

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

```/dbrestadmin/databases/{db_id}/dbs```

Represents databases (dbnames) on a single database server.

### GET

```GET /dbrestadmin/databases/{db_id}/dbs HTTP/1.1```

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

```/dbrestadmin/databases/{db_id}/dbs/{db_name}```

### GET

Not supported

### POST

```POST /dbrestadmin/databases/{db_id}/dbs/{db_name} HTTP/1.1```

Creates a database with {db_name} on database server



