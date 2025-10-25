# 🚀 Railway Deployment - Quick Setup Guide

## Fix "Database connection error" - Step by Step

### Step 1: Access Railway Dashboard

1. Go to https://railway.app/dashboard
2. Log in to your account
3. Find and click on your project: **grad-gallery**

### Step 2: Add MongoDB Service (if not already added)

1. In your project, click **"+ New"**
2. Select **"Database"** → **"Add MongoDB"**
3. Railway will provision a MongoDB instance and auto-generate connection variables

### Step 3: Set Environment Variables

1. Click on your **web service** (the main application)
2. Go to the **"Variables"** tab
3. Add the following variables:

#### Required - MongoDB Connection

If you added Railway's MongoDB plugin, it will auto-create `MONGODB_URI`. If not, add:

```
MONGO_URL=mongodb+srv://<USERNAME>:<PASSWORD>@<CLUSTER>.mongodb.net/ECADYB?retryWrites=true&w=majority
```

**IMPORTANT:** Replace `<USERNAME>`, `<PASSWORD>`, and `<CLUSTER>` with your actual MongoDB credentials!

#### Required - Bunny CDN (for file uploads)

```
BUNNY_STORAGE_ZONE=your_storage_zone_name
BUNNY_ACCESS_KEY=your_access_key_here
BUNNY_CDN_HOST=https://your-cdn.b-cdn.net
```

#### Optional - Email (for password reset feature)

```
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_specific_password
SMTP_FROM_EMAIL=your_email@gmail.com
SMTP_FROM_NAME=Graduation Gallery
SMTP_ENCRYPTION=tls
```

### Step 4: Deploy

1. After adding variables, Railway will **automatically redeploy**
2. Or click the **"Deploy"** button manually
3. Wait 2-3 minutes for the build to complete

### Step 5: Verify

1. Visit your site: https://grad-gallery.up.railway.app
2. The "Database connection error" should be gone!
3. Try logging in to test the connection

---

## Troubleshooting

### Still seeing the error after adding variables?

**Check 1: Verify Variables are Set**

```bash
# In Railway dashboard, check the "Variables" tab
# Make sure MONGO_URL or MONGODB_URI shows the correct value
```

**Check 2: View Deployment Logs**

1. Go to your Railway project
2. Click on the web service
3. Go to **"Deployments"** tab
4. Click on the latest deployment
5. Check logs for error messages

**Check 3: Test MongoDB Connection**

- Use MongoDB Compass or Studio 3T to test your connection string
- Make sure the connection string includes:
  - Correct username and password
  - Correct cluster address
  - Database name: `ECADYB`
  - Network access enabled for Railway's IPs (or allow from anywhere: `0.0.0.0/0`)

**Check 4: Redeploy Manually**

1. Go to your Railway project
2. Click **"Redeploy"** from the menu
3. Wait for the build to complete

### Common MongoDB Connection String Formats

**MongoDB Atlas (Cloud):**

```
mongodb+srv://<USERNAME>:<PASSWORD>@<CLUSTER>.xxxxx.mongodb.net/ECADYB?retryWrites=true&w=majority
```

Replace `<USERNAME>`, `<PASSWORD>`, and `<CLUSTER>` with your actual values.

**Railway MongoDB Plugin:**

```
mongodb://<USERNAME>:<PASSWORD>@<HOSTNAME>:<PORT>/<DATABASE>
```

_(Railway auto-generates this as `MONGODB_URI` - use it directly)_

**Local MongoDB (for testing only):**

```
mongodb://localhost:27017/ECADYB
```

_(No credentials needed for local development)_

---

## Need Help?

### Check Railway Logs

```bash
# If you have Railway CLI installed
railway logs

# Or view logs in Railway dashboard:
# Project → Service → Deployments → Click deployment → View logs
```

### Check Application Error Logs

The application logs MongoDB errors. Check Railway logs for messages like:

- "MongoDB Connection Error in Login.php"
- "Failed to load .env file"
- Connection timeout errors

### MongoDB Network Access

If using MongoDB Atlas:

1. Go to MongoDB Atlas dashboard
2. Network Access → Add IP Address
3. Either add Railway's IPs or allow from anywhere: `0.0.0.0/0`
4. Database Access → Verify user has read/write permissions

---

## Local Development

Want to test locally before deploying?

1. Create a `.env` file in the project root:

```bash
MONGO_URL=mongodb://localhost:27017/ECADYB
BUNNY_STORAGE_ZONE=your_storage_zone
BUNNY_ACCESS_KEY=your_key
BUNNY_CDN_HOST=https://your-cdn.b-cdn.net
```

2. Install dependencies:

```bash
composer install
```

3. Start PHP server:

```bash
php -S localhost:8000
```

4. Visit: http://localhost:8000

---

## Quick Reference

| Variable                     | Purpose             | Required             |
| ---------------------------- | ------------------- | -------------------- |
| `MONGO_URL` or `MONGODB_URI` | Database connection | ✅ Yes               |
| `BUNNY_STORAGE_ZONE`         | File storage zone   | ✅ Yes (for uploads) |
| `BUNNY_ACCESS_KEY`           | CDN access key      | ✅ Yes (for uploads) |
| `BUNNY_CDN_HOST`             | CDN host URL        | ✅ Yes (for uploads) |
| `SMTP_HOST`                  | Email server        | ⚠️ Optional          |
| `SMTP_PORT`                  | Email port          | ⚠️ Optional          |
| `SMTP_USERNAME`              | Email username      | ⚠️ Optional          |
| `SMTP_PASSWORD`              | Email password      | ⚠️ Optional          |
| `SMTP_FROM_EMAIL`            | Sender email        | ⚠️ Optional          |
| `SMTP_FROM_NAME`             | Sender name         | ⚠️ Optional          |
| `SMTP_ENCRYPTION`            | TLS or SSL          | ⚠️ Optional          |

**Note:** Without SMTP variables, password reset won't work, but everything else will function normally.
