# Losode Vendor API

This is a Laravel-based project, completely decoupled from MySQL and Redis. It is configured to run out-of-the-box using **SQLite** for its database, cache, session storage, and queues. 

The application is fully containerized with a simple setup for easy and consistent local development.

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop) installed and running.

## Local Setup

### 1. Environment File

If you don't already have an environment file, copy the example:

```bash
cp .env.example .env
```

Ensure `DB_CONNECTION=sqlite` is set in your `.env`.

### 2. Start the Container

Build the Docker image and start the container as a background daemon:

```bash
docker compose up -d --build
```

### 3. Run Migrations

Once the container is up and running, you need to run the database migrations. The database file `database.sqlite` will be created inside the container if it doesn't already exist.

```bash
docker compose exec app php artisan migrate
```

### 4. Access the Application

The application is now being served right from the Docker container via `php artisan serve`.

It is mapped to port 8000. You can reach it via:

[http://localhost:8000](http://localhost:8000)

## Modifying Dependencies

The container uses standard PHP 8.3 CLI with SQLite plugins. 
If you need to install any new Composer packages, use the executing daemon directly to avoid requiring PHP locally on your machine:

```bash
docker compose exec app composer require [package-name]
```

## Stopping the Application

```bash
docker compose down
```
