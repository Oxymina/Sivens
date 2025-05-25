# Food review Application

A simple review application built with Laravel and Vue.js. This application allows users to register, log in, and view posts sorted by categories. Administrators can manage posts, categories, and tags.

## Table of Contents

- [Features](#features)
- [Technologies Used](#technologies-used)
- [Installation](#installation)
- [Usage](#usage)
- [API Endpoints](#api-endpoints)
- [License](#license)

## Features

- User registration and authentication
- View and filter posts by categories
- Manage posts, categories, and tags

## Technologies Used

- **Backend**: Laravel 9
- **Frontend**: Vue.js 3, Vuetify
- **Database**: MySQL
- **Authentication**: Laravel Passport

## Installation

### Prerequisites

- PHP 7.4 or higher
- Composer
- Node.js and npm
- MySQL

### Backend Setup

1. Clone the repository:

   ```bash
   git clone https://github.com/yourusername/blog-application.git
   cd blog-application
   ```

2. Install backend dependencies:

   ```bash
   composer install
   ```

3. Create a copy of `.env.example` and rename it to `.env`. Update the environment variables as needed:

   ```bash
   cp .env.example .env
   ```

4. Generate an application key:

   ```bash
   php artisan key:generate
   ```

5. Configure your database in the `.env` file:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

6. Run the database migrations and seeders:

   ```bash
   php artisan migrate --seed
   ```

7. Install Passport for API authentication:

   ```bash
   php artisan passport:install
   ```

8. Start the backend server:

   ```bash
   php artisan serve
   ```

### Frontend Setup

1. Navigate to the `frontend` directory:

   ```bash
   cd frontend
   ```

2. Install frontend dependencies:

   ```bash
   npm install
   ```

3. Start the frontend development server:

   ```bash
   npm run serve
   ```

## Usage

### Accessing the Application

- Open your browser and navigate to `http://localhost:8000` for the backend.
- The frontend will typically be available at `http://localhost:8080`.

### Authentication

- Register a new user or log in with an existing account.
- After logging in, you can view and filter blog posts by categories.

### Admin Features

- Admin users can create, update, and delete posts, categories, and tags via the API.

## API Endpoints

### Authentication

- `POST /api/login`: Log in a user
- `POST /api/logout`: Log out the authenticated user

### Posts

- `GET /api/posts`: Retrieve all posts
- `GET /api/posts/{id}`: Retrieve a single post by ID
- `POST /api/posts`: Create a new post
- `PUT /api/posts/{id}`: Update an existing post
- `DELETE /api/posts/{id}`: Delete a post

### Categories

- `GET /api/categories`: Retrieve all categories
- `GET /api/categories/{id}`: Retrieve a single category by ID
- `POST /api/categories`: Create a new category
- `PUT /api/categories/{id}`: Update an existing category
- `DELETE /api/categories/{id}`: Delete a category

### Tags

- `GET /api/tags`: Retrieve all tags
- `GET /api/tags/{id}`: Retrieve a single tag by ID
- `POST /api/tags`: Create a new tag
- `PUT /api/tags/{id}`: Update an existing tag
- `DELETE /api/tags/{id}`: Delete a tag

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

This README provides an overview of the project, installation steps, and usage instructions, ensuring that anyone can set up and use the blog application efficiently.