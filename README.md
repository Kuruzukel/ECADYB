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

*For Railway (Recommended):*
- `SENDGRID_API_KEY` - SendGrid API key for email delivery
- `SMTP_FROM_EMAIL` - Sender email address
- `SMTP_FROM_NAME` - Sender display name

*For Localhost (Alternative):*
- `SMTP_HOST` - SMTP server hostname (e.g., smtp.gmail.com)
- `SMTP_PORT` - SMTP port (587 or 465)
- `SMTP_USERNAME` - Authentication username
- `SMTP_PASSWORD` - Authentication password (use app-specific password for Gmail)
- `SMTP_FROM_EMAIL` - Sender email address
- `SMTP_FROM_NAME` - Sender display name
- `SMTP_ENCRYPTION` - Encryption protocol (tls/ssl)

**Note:** Railway blocks SMTP ports, so SendGrid is required for production deployment.

## Deployment

### Railway Platform

1. Connect your GitHub repository to Railway
2. Configure environment variables in the Railway dashboard:
   - `MONGO_URL` - MongoDB connection string
   - `BUNNY_STORAGE_ZONE`, `BUNNY_ACCESS_KEY`, `BUNNY_CDN_HOST` - CDN configuration
   - `SENDGRID_API_KEY` - SendGrid API key (required for email)
   - `SMTP_FROM_EMAIL`, `SMTP_FROM_NAME` - Email sender details
3. Deploy automatically with included configuration files

The application supports both Nixpacks and Docker deployment strategies. See `RAILWAY_SETUP.md` for detailed setup instructions.

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
- SendGrid API for email delivery (Railway)
- PHPMailer for SMTP email (localhost fallback)

## Documentation

- `RAILWAY_SETUP.md` - Detailed deployment guide
- `DEPLOYMENT.md` - General deployment instructions
- `SECURITY.md` - Security guidelines
