# Graduation Gallery Application

A modern web application for managing graduation yearbooks and student galleries, built with PHP and MongoDB.

## Overview

This application provides a comprehensive platform for educational institutions to manage digital yearbooks, student profiles, and graduation galleries with secure authentication and media management capabilities.

## Environment Configuration

### Required Variables

**Database**

- `MONGO_URL` or `MONGODB_URI` - MongoDB connection string
  - Format: `mongodb+srv://[username]:[password]@[cluster]/[database]`

**CDN Storage**

- `BUNNY_STORAGE_ZONE` - Storage zone name
- `BUNNY_ACCESS_KEY` - API access key
- `BUNNY_CDN_HOST` - CDN host URL

**Email Service**

- `SMTP_HOST` - SMTP server hostname
- `SMTP_PORT` - SMTP port (587 or 465)
- `SMTP_USERNAME` - Authentication username
- `SMTP_PASSWORD` - Authentication password
- `SMTP_FROM_EMAIL` - Sender email address
- `SMTP_FROM_NAME` - Sender display name
- `SMTP_ENCRYPTION` - Encryption protocol (tls/ssl)

## Deployment

### Railway Platform

1. Connect your GitHub repository to Railway
2. Configure environment variables in the Railway dashboard
3. Deploy automatically with included configuration files

The application supports both Nixpacks and Docker deployment strategies.

### Local Development

```bash
# Install dependencies
composer install

# Configure environment
cp .env.example .env

# Edit .env with your credentials
# Start development server
php -S localhost:8000
```

## Project Structure

```
Connection/     Database operations and API endpoints
Admin/          Administrative interface
Student/        Student portal
LandingPage/    Public-facing interface
vendor/         PHP dependencies
```

## Technical Stack

- PHP 7.4+
- MongoDB with PHP extension
- Composer dependency management
- Bunny CDN for media storage
- PHPMailer for email functionality

## Documentation

- `RAILWAY_SETUP.md` - Detailed deployment guide
- `DEPLOYMENT.md` - General deployment instructions
- `SECURITY.md` - Security guidelines
