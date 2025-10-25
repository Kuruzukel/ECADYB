# Railway Deployment Guide

This guide provides multiple deployment options for the ECADYB application on Railway.

## Option 1: Nixpacks (Recommended)

Railway will automatically detect your PHP application and install the MongoDB extension.

### Steps:

1. Push your code to GitHub
2. Connect your repository to Railway
3. Set the `MONGO_URL` environment variable in Railway dashboard
4. Deploy

### Configuration Files:

- `railway.json` - Uses Nixpacks builder
- `nixpacks.toml` - Simplified PHP configuration
- `composer.json` - Dependencies with platform-check disabled

## Option 2: Docker

If Nixpacks fails, you can use the Docker deployment.

### Steps:

1. Update `railway.json` to use Docker:

```json
{
  "build": {
    "builder": "DOCKERFILE",
    "dockerfilePath": "Dockerfile"
  }
}
```

2. Deploy

### Configuration Files:

- `Dockerfile` - Complete PHP + MongoDB setup
- `docker/apache.conf` - Apache configuration

## Environment Variables

### Required Environment Variables

**CRITICAL:** Make sure to set these in Railway dashboard before deploying:

#### MongoDB Configuration (REQUIRED)

- `MONGO_URL` or `MONGODB_URI`: Your MongoDB connection string
  - Get from MongoDB Atlas dashboard (connection string) or Railway MongoDB plugin
  - The connection string format is: `mongodb+srv://[user]:[pass]@[host]/[db]`
  - If you're using Railway's MongoDB plugin, it will automatically set `MONGODB_URI`
  - The application checks both `MONGO_URL` and `MONGODB_URI`
  - **Never commit this value to Git - set it only in Railway dashboard**

#### Bunny CDN Configuration (Required for file uploads)

- `BUNNY_STORAGE_ZONE`: Your Bunny.net storage zone name
- `BUNNY_ACCESS_KEY`: Your Bunny.net access key
- `BUNNY_CDN_HOST`: Your CDN host URL (e.g., `https://your-cdn.b-cdn.net`)

#### Email Configuration (Required for password reset)

- `SMTP_HOST`: SMTP server hostname (e.g., `smtp.gmail.com`)
- `SMTP_PORT`: SMTP port (e.g., `587` for TLS, `465` for SSL)
- `SMTP_USERNAME`: Your email address
- `SMTP_PASSWORD`: Your email password or app-specific password
- `SMTP_FROM_EMAIL`: Email address to send from
- `SMTP_FROM_NAME`: Display name for emails (e.g., `"Graduation Gallery"`)
- `SMTP_ENCRYPTION`: Encryption method (`tls` or `ssl`)

### Setting Environment Variables in Railway

1. Go to your Railway dashboard: https://railway.app/dashboard
2. Select your project (grad-gallery)
3. Go to the "Variables" tab
4. Add each environment variable listed above
5. Click "Deploy" or wait for auto-redeploy

## Troubleshooting

### "Database connection error" on Login Page

**Symptoms:** Red error message saying "Database connection error. Please contact the administrator or try again later."

**Solution:**

1. Check if `MONGO_URL` or `MONGODB_URI` is set in Railway dashboard
2. Verify the MongoDB connection string is correct
3. Test the connection string using MongoDB Compass or similar tool
4. Check Railway logs for detailed error messages: `railway logs`
5. If using Railway's MongoDB plugin, ensure it's linked to your service

**Common causes:**

- Environment variable not set
- Incorrect connection string format
- MongoDB server is not accessible
- Network/firewall issues

### Composer Install Fails

- The `--ignore-platform-reqs` flag is used to bypass extension checks during installation
- Railway will install the MongoDB extension automatically

### MongoDB Connection Issues

- Verify `MONGO_URL` or `MONGODB_URI` is set correctly in Railway environment variables
- Check that all connection files use `getenv('MONGO_URL')` instead of hardcoded localhost
- Ensure your MongoDB cluster allows connections from Railway's IP addresses
- Check if the database name in your connection string matches your actual database

### Build Failures

- Try switching between Nixpacks and Docker builders
- Check Railway logs for specific error messages
- Verify all dependencies in `composer.json` are compatible

## Local Testing

Test your deployment locally:

```bash
# Test MongoDB connection
php test-mongodb.php

# Run development server
php -S localhost:8000 -t .
```

## Files Updated for Deployment

All MongoDB connection files have been updated to use environment variables:

- `Connection/Configuration/MongoFetch.php`
- `Connection/FetchAnnouncement.php`
- `Connection/DeleteAnnouncement.php`
- `Connection/TestAnnouncement.php`
- `Connection/SubmitAnnouncement.php`
- `Connection/DeleteStudent.php`
- `Admin/Components/AddNewStudent.php`
- `Admin/Components/BatchUpload.php`
- `Admin/Components/StudentList.php`
- `Admin/Components/EditStudentInformation.php`
