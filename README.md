# Technical Test API Invoice

This repository contains the Laravel API Invoice project for the technical test. It provides CRUD operations for customers and products, as well as the ability to create and update invoices. Additionally, it supports retrieving all invoices and retrieving invoices by their unique code.

## Features

- CRUD operations for customers
- CRUD operations for products
- Create and update invoices
- Retrieve all invoices
- Retrieve invoices by code

## Prerequisites

- PHP 7.4 or higher
- Composer
- Laravel framework
- MySQL 5.7 (database)

## Setup

1. Clone the repository:
```bash
git clone https://github.com/siskastev/api-invoice.git
```
2. Navigate to the project directory:
```bash
cd api-invoice
```
3. Rename .env.example to .env
4. Generate the application key:
```bash
php artisan key:generate
```
5. Install the dependencies:
```bash
composer install
```
6. Navigate to the migrations and seed the database:
```bash
php artisan migrate --seed
```
7. Start the development server:
```bash
php artisan serve
```

## API Endpoints
### Register
* `POST /api/register`: Create a new register user.
### Login
* `POST /api/login`: Login user.
### Profile
* `GET /api/profile`: Profile user.
### Logout
* `POST /api/logout`: Logout user.

### Customers Admin Role
* `GET /api/customers`: Retrieve all customers.
* `GET /api/customers/{id}`: Retrieve a specific customer by ID.
* `POST /api/customers`: Create a new customer.
* `PUT /api/customers/{id}`: Update an existing customer.
* `DELETE /api/customers/{id}`: Delete a customer.

### Products Admin Role
* `GET /api/products`: Retrieve all products.
* `GET /api/products/{id}`: Retrieve a specific customer by ID.
* `POST /api/products`: Create a new product(Admin role required).
* `PUT /api/products/{id}`: Update an existing product(Admin role required).
* `DELETE /api/products/{id}`: Delete a product (Admin role required).

### Invoice Admin Role
* `GET /api/invoice`: Retrieve all invoice.
* `GET /api/invoice/{code}`: Retrieve a specific invoice by code.
* `POST /api/invoice`: Create a new invoice.
* `PUT /api/invoice/{code}`: Update an existing invoice.
* `DELETE /api/invoice/{code}`: Delete a invoice.

## Postman Collection
A Postman collection `API-INVOICE-LARAVEL.postman_collection.json` and env for token `laravel-api-invoice.postman_environment` is provided in this project. You can import the collection into Postman to quickly test the API endpoints. The collection includes pre-configured requests for each endpoint, allowing you to easily interact with the API and view the responses.

## Authentication
The API uses Laravel Sanctum for API token authentication. To authenticate requests, include the API token in the request headers:
`Authorization: Bearer {api_token}`. To log in as the admin user, use the following credentials:
* `Email`: admin@gmail.com
* `Password`: admin






