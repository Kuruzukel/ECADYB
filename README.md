# GRADUATION GALLERY APPLICATION

A PHP application with MongoDB integration for managing graduation yearbooks and student galleries, designed to be deployed on Railway.

## Deployment on Railway

### Prerequisites

- Railway account
- MongoDB service added to your Railway project

### Environment Variables

Make sure to set the following environment variables in your Railway project:

- `MONGO_URL`: Your Railway MongoDB connection string
- `BUNNY_STORAGE_ZONE`: Your Bunny CDN storage zone name
- `BUNNY_ACCESS_KEY`: Your Bunny CDN API access key
- `BUNNY_CDN_HOST`: Your Bunny CDN host URL

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
