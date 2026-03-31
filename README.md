# Task Management API

A Laravel-based REST API for managing tasks with priority sorting and status transition logic.

##Getting Started

1. **Install dependencies:** `composer install`

2. **Setup environment:** `cp .env.example .env` (Update DB credentials)
        ``` 
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=task_management
        DB_USERNAME=yourUsername
        DB_PASSWORD=yourPassword
        ```

3. **Run migrations:** `php arisan migrate`
                        `php artisan make:migrations`
                        `php artisan migrate:fresh --seed`

4. **Start server:** `php artisan serve`

---

## 🛠 API Endpoints

## Testing in Postman
 
## Headers

For all API requests, include the following headers:

```http
Content-Type: application/json
Accept: application/json
```

### 1. Create a Task
* **URL:** `POST http://localhost:8000/api/api/tasks`
* **Body (JSON):**
    ```json
    {
        "title": "New Task",
        "due_date": "2026-04-01",
        "priority": "high"
    }
    ```

### 2. List Tasks
* **URL:** `GET api/api/tasks`
* **Sorting:** Automatically sorts by **Priority (High -> Low)** then **Due Date (Asc)**.

### 3. Update Status (URL-based)
* **URL:** `PATCH /api/api/tasks/{id}/status`
* **Example:** `PATCH http://localhost:8000/api/api/tasks/6/status?status=in_progress`
* **Note:** Only allows transitions `pending -> in_progress -> done`.

### 4. Daily Report (URL-based)
* **URL:** `GET /api/tasks/report/{date}`
* **Example:** `GET http://localhost:8000/api/tasks/report?date=2026-04-01`
* **Response:** Returns a summary of tasks grouped by priority and status.

### 5. Delete Task
* **URL:** `DELETE /api/tasks/{id}`
* **Example:** `DELETE http://localhost:8000/api/tasks/6`

---

## 🚦 Business Rules
* **Uniqueness:** A task `title` must be unique for a specific `due_date`.
* **Dates:** `due_date` must be today or in the future.
* **Transitions:** You cannot skip statuses (e.g., cannot go from `pending` straight to `done`).

## Deployment on Railway

This project can be deployed بسهولة on Railway using Nixpacks.

---

### Prerequisites

- GitHub account
- Railway account (https://railway.app)
- Laravel project pushed to GitHub

---

### Step 1: Add Nixpacks Configuration

Railway uses PHP 8.3 by default, but this project requires **PHP 8.4+**.

Create a file in the root of your project:

```bash
nixpacks.toml
```
## Deployment on Railway

Follow these steps to deploy your Laravel API on Railway.

---

### Step 1: Configure Nixpacks (PHP 8.4)

Create a file in the root of your project:

```bash
nixpacks.toml
```
### Add the following configuration:

```[phases.setup]
nixPkgs = ["php84", "php84Packages.composer"]

[phases.build]
cmds = [
  "composer install --no-dev --optimize-autoloader",
  "php artisan key:generate",
  "php artisan migrate --force"
]

[start]
cmd = "php artisan serve --host 0.0.0.0 --port $PORT"
```
### Step 2: Environment Variables

- In the Railway dashboard -> projects -> Variables tab, add:

```APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your_app_key

DB_CONNECTION=mysql
DB_HOST=your_db_host
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
NIXPACKS_PHP_VERSION=8.4
```
- You can add a MySQL plugin in Railway and copy the credentials.

### Step 3: Deploy
- Go to Railway dashboard
- Click New Project
- Select Deploy from GitHub Repo
- Choose your repository

- Railway will automatically build and deploy your application.