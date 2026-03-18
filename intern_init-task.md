Backend Intern Initial Task
### Simple CRUD API – Users and Tasks

## Objective

Create a simple **REST API** using **Laravel** for managing **Users and Tasks**.

This task will introduce or refresh your understanding of:

- Laravel routing
- Controllers
- Request validation
- Eloquent ORM
- Database migrations
- API responses
- Model relationships

Authentication is **NOT required**.

## Base API URL

All endpoints are prefixed with:

`/api`

Example:

GET /api/users

---

# Requirements

## 1. Create User Entity *DONE*

A **User** should have the following fields:

| Field | Type | Required | Notes |
|------|------|----------|------|
| id | bigint | yes | primary key |
| name | string | yes | max 255 characters |
| email | string | yes | must be unique |
| created_at | timestamp | auto | |
| updated_at | timestamp | auto | |

---

## 2. User CRUD  *DONE*

### Create User

**Endpoint**

POST /api/users

**Payload **

name: Juan Dela Cruz  
email: juan@example.com  

**Expected Response**

message: User created successfully  
data: id, name, email  

---

### List Users

**Endpoint**

GET /api/users

Returns all users.

---

### View User

**Endpoint**

GET /api/users/{id}

Example

GET /api/users/1

If the user does not exist

404 User not found

---

### Update User

**Endpoint**

PUT /api/users/{id}

**Example Payload**

name: Juan Updated

---

### Delete User

**Endpoint**

DELETE /api/users/{id}

**Response**

message: User deleted successfully

---

# Task Management

## 3. Create Task Entity

A **Task** belongs to a **User**.

| Field | Type | Required | Notes |
|------|------|----------|------|
| id | bigint | yes | primary key |
| user_id | bigint | yes | foreign key to users |
| title | string | yes | max 255 characters |
| description | text | no | optional |
| status | enum | yes | pending, in_progress, completed |
| due_date | date | no | optional |
| created_at | timestamp | auto | |
| updated_at | timestamp | auto | |

Relationship

User has many Tasks  
Task belongs to User  

---

## 4. Create Task

When creating a task, the **user_id must be provided in the request**.

**Endpoint**

POST /api/tasks

**Payload**

user_id: 1  
title: Finish onboarding task  
description: Create a CRUD API using Laravel  
status: pending  
due_date: 2026-03-20  

**Validation Rules**

user_id must exist in users table  
title is required  
status is required  

Allowed status values:

- pending
- in_progress
- completed

**Expected Response**

message: Task created successfully  
data: id, user_id, title, description, status, due_date  

---

## 5. List Tasks

**Endpoint**

GET /api/tasks

Returns all tasks.

Optional filtering by status

GET /api/tasks?status=pending

Optional filtering by user

GET /api/tasks?user_id=1

---

## 6. View Task

**Endpoint**

GET /api/tasks/{id}

Example

GET /api/tasks/1

If the task does not exist

404 Task not found

---

## 7. Update Task

**Endpoint**

PUT /api/tasks/{id}

Fields that may be updated:

- title
- description
- status
- due_date

Example payload

title: Finish onboarding task v2  
status: in_progress  

---

## 8. Delete Task

**Endpoint**

DELETE /api/tasks/{id}

Response

message: Task deleted successfully

---

# Technical Requirements

Implement the following:

- Create migrations *DONE*
- Create User and Task models *DONE*
- Create UserController *DONE*
- Create TaskController *DONE*
- Use Form Request validation *DONE*
- Implement RESTful routing *DONE (switched to resource routing*)
    - You may use **resource routing** or define **routes manually using HTTP methods**
- All API responses must be returned in **JSON format** 

---

# Bonus (Optional)

Pagination

GET /api/tasks?page=1

Filter by status

GET /api/tasks?status=completed

Sort by due date

GET /api/tasks?sort=due_date

---

# Submission Requirements

Provide the following:

- GitHub repository
- Migration files
- Models
- Controllers
- Form Request validation
- Postman collection or API documentation
- README with setup instructions
