# How to Set up:
### Guide on how to set up Laravel simple CRUD for Users and Tasks management

## Requirements
- PHP >= 8.2
- Composer >= 2.5.4
- Node >= 16.16

## Applications Used
#### 
- Visual Studio Code
- Herd
- Postman
- XAMPP (MySQL)
- HeidiSQL

## Installation Steps
#### Step by step guide on installing and setting up the Laravel project
- **Step 1:** Clone the repository:
    Open Github Desktop, clone the repository by pasting the URL.
- **Step 2:** Set up database:
    In HeidiSQL, add a new database named 'crud-api'.
    Run `php artisan migrate` in VSC terminal
- **Step 3:** Install Composer:
    `composer install`
- **Step 4:** Test the project in using any API tester such as Postman.
    For Postman tester, download and import as collection the postman file provided and test the functionalities.



## POSTMAN Testing
#### Guide on how to test the project using Postman
### USERS
#### GET method - Read data from a server.
- url: 'http://crud-api-task.test/api/users'
#### POST method - Create a new resource.
-   url: 'http://crud-api-task.test/api/users'
- body (raw, JSON): 
```
{
    "name": "Taylor Swift",
    "email": "sample@gmail.com"
}
```
#### PUT method - Replace an existing resource entirely.
- url: 'http://crud-api-task.test/api/users/:id'
- body (raw, JSON):
```
{
    "name": "Taylor Shift",
    "email": "sample111@gmail.com"
}
```
#### DELETE method - Remove/delete an existing user data.
- url: http://crud-api-task.test/api/users/:id

### TASKS
#### GET method - Read data from a server.
- url: 'http://crud-api-task.test/api/tasks'
#### GET method (filters)
- filter by status (pending, in_progress, completed). url: 'http://crud-api-task.test/api/tasks?status=pending'
- sort task by due_date. url: 'http://crud-api-task.test/api/tasks?sort=due_date'
- pagination filter. url: 'http://crud-api-task.test/api/tasks?page=1'


#### POST method - Create a new tasks.
- url: 'http://crud-api-task.test/api/tasks'
- body (raw, JSON): 
```
{
    "user_id": 1,
    "title": "CRUD api task",
    "description": "Create a simple REST API using Laravel for managing Users and Tasks",
    "status": "pending",
    "due_date": ""
}
```
#### PUT method - Replace an existing resource entirely.
- url: 'http://crud-api-task.test/api/tasks/:id'
- body (raw, JSON):
```
{
    "user_id": 1,
    "title": "CRUD api task v2",
    "description": "Create a simple REST API using Laravel for managing Users and Tasks",
    "status": "completed",
    "due_date": ""
}

```
#### DELETE method - Remove/delete an existing task.
- url: 'http://crud-api-task.test/api/users/:id'

