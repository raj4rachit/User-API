# Laravel 11 User Management API

This repository contains a Laravel 11 API for comprehensive user management, including user registration, authentication, role assignment, and permissions management. Ideal for developers looking to integrate robust user management functionalities into their applications.

## Features

-   User Registration
-   User Authentication (Login/Logout)
-   Role Management
-   Permission Management
-   Middleware for Role and Permission Based Access Control

## Requirements

-   PHP >= 8.1
-   Composer
-   Laravel 11
-   MySQL or any other supported database

## Installation

1. Clone the repository:

    ```bash
    git clone https://github.com/yourusername/laravel11-user-management-api.git
    ```

2. Navigate to the project directory:

    ```bash
    cd laravel11-user-management-api
    ```

3. Install dependencies:

    ```bash
    composer install
    ```

4. Copy the example environment file and set the environment variables:

    ```bash
    cp .env.example .env
    ```

    Update the `.env` file with your database and mail server configurations.

5. Generate the application key:

    ```bash
    php artisan key:generate
    ```

6. Run the database migrations:

    ```bash
    php artisan migrate
    ```

7. Seed the database

    ```bash
    php artisan migrate:fresh --seed
    ```

8. Start the development server:

    ```bash
    php artisan serve
    ```

## API Endpoints

### Authentication

-   **Login**: `POST /v1/login`
-   **Logout**: `POST /v1/logout` (Authenticated)

### User Management

-   **Get Me Details**: `GET /v1/me` (Authenticated)
-   **Get User List**: `GET /v1/users` (Authenticated)
-   **Get User Details**: `GET /v1/users/{id}` (Authenticated)
-   **Post User Profile**: `POST /v1/users` (Authenticated) (infos)
-   **Update User Profile**: `PUT /v1/users/{id}` (Authenticated)

### Role Management

-   **Get All Roles**: `GET /v1/roles` (Authenticated, Admin)
-   **Create Role**: `POST /v1/roles` (Authenticated, Admin)
-   **Update Role**: `PUT /v1/roles/{id}` (Authenticated, Admin)
-   **Delete Role**: `DELETE /v1/roles/{id}` (Authenticated, Admin)

### Permission Management

-   **Assign Permission to Role**: `PUT /v1/roles json
{
        "name": "Yeni Rol",
        "guard_name": "web",
        "permission_ids": [2],
        "user_ids": [2]
}`

## Middleware

-   laravel/passport

## Author

This project was created and is maintained by Beytullah YAŞAR. If you have any questions or suggestions, feel free to reach out!

-   GitHub: [beytt06](https://github.com/beytt06/
-   Email: [beytullahyasar06@gmail.com](mailto:beytullahyasar06@gmail.com)

## License

This project is open-source and licensed under the MIT License.

```

```
