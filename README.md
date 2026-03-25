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

- **Step 2:** Install dependencies:
    Run `composer install` in the VSCode terminal.

- **Step 3:** Set up environment configuration:
    Run `cp .env.example .env` in the terminal.
    Then run `php artisan key:generate`.
    Open `.env` and set your database credentials:
        DB_DATABASE=crud-api
        DB_USERNAME=root
        DB_PASSWORD= 

- **Step 4:** Set up database:
    Using MySQL, create a database named 'crud-api'.
    Then run `php artisan migrate` in the VSCode terminal.

- **Step 5:** Test the API endpoints using any API tester such as Postman or Swagger.
    Download and import the provided Postman collection file and test the functionalities.
    Or open 'http://crud-api-task.test/swagger'


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

