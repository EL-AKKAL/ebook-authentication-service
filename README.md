## About Project

This project is a dedicated Authentication Microservice built as part of a scalable microservices architecture.

The service is responsible for:

- User registration

- Secure login

- JWT token issuance & refresh

- Authenticated user retrieval

- Logout & token invalidation

- Password reset (email-based flow)

It acts as the central identity provider for other microservices and propagates identity using JWT tokens.

- [see api endpoints here](https://ebook-authentication-service.elakkalayoub.cloud/).

## Installation & Usage

### Requirements

- PHP 8.2+
- Composer
- MySQL (or SQLite for testing)
- Node.js (for frontend)
- Mail server (SMTP or local dev mail)
- Git

### Clone the Repository

```
git clone https://github.com/EL-AKKAL/ebook-authentication-service.git
cd ebook-authentication-service
```

### Install Dependencies

```
composer install
```

### Environment Setup

```
cp .env.example .env
```

Then configure the environment variables.

### Required Configuration

- Frontend URL (for password reset links)

```
APP_FRONTEND_URL=
```

- Mail Configuration

```
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

- Queue Configuration

```
QUEUE_CONNECTION=rabbitmq
```

- RabbitMQ Configuration

```
#rabbitmq config / see config/queue
RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
```

- JWT Configuration
  This secret must be shared across all microservices:

```
JWT_SECRET=

#Generate it:
php artisan jwt:secret
```

Generate the application key:

```
php artisan key:generate
```

- Database Setup

Configure your database connection in .env, then run:

```
php artisan migrate
```

### Run the Server

```
php artisan serve
```

Save the URL printed in the terminal — you’ll use it in your frontend configuration.

## Architecture Principles

- Microservice-first architecture — isolated authentication boundary

- JWT-based identity propagation across services

- Clean separation of concerns

- Event-driven communication via RabbitMQ

- Secure password reset workflow with token validation

- Test-driven development using Pest

- CI-ready with GitHub Actions

## Authentication Domain Features

- User registration

- Secure login with JWT token issuance

- Token refresh

- Authenticated user retrieval (/me)

- Logout & token invalidation

- Email-based password reset

- Event dispatching (e.g., UserRegistered)

### Testing Strategy (PestPHP)

The project includes feature-level API tests covering:

- Successful & failed registration

- Duplicate email validation

- Successful & failed login

- JWT-protected route enforcement

- Authenticated user retrieval

- Logout & token refresh

- Password reset flow (valid & invalid tokens)

- Notification dispatch verification

Reusable helpers provide:

- Authenticated test users

- JWT token generation

- Clean database state via RefreshDatabase

## CI/CD Pipeline

The project integrates GitHub Actions to:

- Run tests automatically on every push
- Inject secrets securely (JWT_SECRET)
- Prevent deployments if tests fail
- Deploy safely to a VPS via SSH

This guarantees continuous quality enforcement and safe delivery.
