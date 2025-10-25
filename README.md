# GRADUATION GALLERY APPLICATION

A PHP application with MongoDB integration for managing graduation yearbooks and student galleries, designed to be deployed on Railway.

## Deployment on Railway

### 🚨 CRITICAL: Fix "Database connection error"

If you're seeing a "Database connection error" on your deployed site, you need to set environment variables in Railway:

**Quick Fix Steps:**

1. Go to [Railway Dashboard](https://railway.app/dashboard)
2. Select your project → Variables tab
3. Add `MONGO_URL` or `MONGODB_URI` with your MongoDB connection string
4. Wait for auto-redeploy (or click "Deploy")

### Prerequisites

- Railway account
- MongoDB service added to your Railway project

### Required Environment Variables

**MongoDB (REQUIRED):**

- `MONGO_URL` or `MONGODB_URI`: Your MongoDB connection string
  - Format: `mongodb+srv://<USERNAME>:<PASSWORD>@<CLUSTER>.mongodb.net/<DATABASE>?retryWrites=true&w=majority`
  - Example: `mongodb+srv://dbuser:YOUR_PASSWORD_HERE@cluster0.abc123.mongodb.net/ECADYB?retryWrites=true&w=majority`
  - If using Railway's MongoDB plugin, it will auto-set `MONGODB_URI`
  - **Replace all placeholders with your actual credentials**

**Bunny CDN (Required for file uploads):**

- `BUNNY_STORAGE_ZONE`: Your Bunny CDN storage zone name
- `BUNNY_ACCESS_KEY`: Your Bunny CDN API access key
- `BUNNY_CDN_HOST`: Your Bunny CDN host URL (e.g., `https://your-cdn.b-cdn.net`)

**Email (Required for password reset):**

- `SMTP_HOST`: SMTP server (e.g., `smtp.gmail.com`)
- `SMTP_PORT`: Port number (e.g., `587`)
- `SMTP_USERNAME`: Your email address
- `SMTP_PASSWORD`: Your email password or app-specific password
- `SMTP_FROM_EMAIL`: Email to send from
- `SMTP_FROM_NAME`: Display name (e.g., `"Graduation Gallery"`)
- `SMTP_ENCRYPTION`: `tls` or `ssl`

### Deployment Steps

1. Connect your GitHub repository to Railway
2. Railway will automatically detect the PHP application and install the MongoDB extension
3. The application will use the `MONGO_URL` environment variable to connect to your MongoDB service
4. Deploy and your application will be available at the provided Railway URL

### Troubleshooting Deployment Issues

If you encounter deployment errors:

1. Railway will automatically install the MongoDB PHP extension
2. The `composer.json` file is configured to ignore platform requirements during installation
3. All connection files have been updated to use environment variables instead of hardcoded localhost
4. Both Docker and Nixpacks configurations are available as deployment options

### Local Development

To run locally:

1. Install Composer dependencies: `composer install`
2. Copy `.env.example` to `.env` and configure your environment variables:
   ```bash
   cp .env.example .env
   ```
3. Edit `.env` and set your configuration:
   - `MONGO_URL`: Your MongoDB connection string
   - `BUNNY_STORAGE_ZONE`: Your Bunny CDN storage zone name
   - `BUNNY_ACCESS_KEY`: Your Bunny CDN API access key
   - `BUNNY_CDN_HOST`: Your Bunny CDN host URL
4. Run with PHP's built-in server: `php -S localhost:8000`

**Important:** Never commit the `.env` file to version control. It's already included in `.gitignore`.

### File Structure

- `Connection/`: MongoDB connection and data operations
- `Admin/`: Administrative interface components
- `LandingPage/`: Main application interface
- `vendor/`: Composer dependencies (including MongoDB PHP driver)

### Configuration Files

- `railway.json`: Railway deployment configuration (Nixpacks)
- `Dockerfile`: Alternative Docker deployment configuration
- `nixpacks.toml`: Nixpacks build configuration
- `composer.json`: PHP dependencies
- `.railwayignore`: Files to exclude from deployment
