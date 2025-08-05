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

Make sure to set in Railway dashboard:
- `MONGO_URL`: Your Railway MongoDB connection string

## Troubleshooting

### Composer Install Fails
- The `--ignore-platform-reqs` flag is used to bypass extension checks during installation
- Railway will install the MongoDB extension automatically

### MongoDB Connection Issues
- Verify `MONGO_URL` is set correctly in Railway environment variables
- Check that all connection files use `getenv('MONGO_URL')` instead of hardcoded localhost

### Build Failures
- Try switching between Nixpacks and Docker builders
- Check Railway logs for specific error messages

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
- `Connection/MongoFetch.php`
- `Connection/FetchAnnouncement.php`
- `Connection/DeleteAnnouncement.php`
- `Connection/TestAnnouncement.php`
- `Connection/SubmitAnnouncement.php`
- `Connection/DeleteStudent.php`
- `Admin/Components/AddNewStudent.php`
- `Admin/Components/BatchUpload.php`
- `Admin/Components/StudentList.php`
- `Admin/Components/EditStudentInformation.php` 