<div align="center">

# 🚂 Railway Deployment Guide

### *Complete deployment instructions for ECADYB on Railway*

![Railway](https://img.shields.io/badge/Railway-0B0D0E?style=for-the-badge&logo=railway&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Nixpacks](https://img.shields.io/badge/Nixpacks-79FFE1?style=for-the-badge)

</div>

---

## 📋 Table of Contents

- [Deployment Options](#-deployment-options)
- [Environment Variables](#-environment-variables)
- [Troubleshooting](#-troubleshooting)
- [Local Testing](#-local-testing)

---

## 🚀 Deployment Options

### ⚡ Option 1: Nixpacks (Recommended)

Railway will **automatically detect** your PHP application and install the MongoDB extension.

<table>
<tr><td>

#### 📝 Steps:

1. 📤 **Push your code to GitHub**
   ```bash
   git push origin main
   ```

2. 🔗 **Connect your repository to Railway**
   - Go to [Railway Dashboard](https://railway.app/dashboard)
   - Click "New Project" → "Deploy from GitHub repo"
   - Select your repository

3. ⚙️ **Set environment variables**
   - Navigate to "Variables" tab
   - Add `MONGO_URL` and other required variables
   - See [Environment Variables](#-environment-variables) section below

4. 🎉 **Deploy**
   - Railway will automatically build and deploy
   - Wait for deployment to complete

</td></tr>
</table>

#### 📂 Configuration Files:

| File | Purpose |
|------|---------|
| `railway.json` | Specifies Nixpacks builder |
| `nixpacks.toml` | Simplified PHP configuration |
| `composer.json` | Dependencies (platform-check disabled) |

---

### 🐳 Option 2: Docker

If Nixpacks fails, use the Docker deployment method.

<table>
<tr><td>

#### 📝 Steps:

1. 🔧 **Update `railway.json`** to use Docker:

```json
{
  "build": {
    "builder": "DOCKERFILE",
    "dockerfilePath": "Dockerfile"
  }
}
```

2. 🚀 **Deploy**
   - Push changes to GitHub
   - Railway will rebuild using Docker

</td></tr>
</table>

#### 📂 Configuration Files:

| File | Purpose |
|------|---------|
| `Dockerfile` | Complete PHP + MongoDB setup |
| `docker/apache.conf` | Apache web server configuration |

---

## 🔐 Environment Variables

> **⚠️ CRITICAL:** Set all required variables in Railway dashboard **before** deploying!

### 🗄️ MongoDB Configuration (REQUIRED)

<table>
<tr>
<td width="30%"><b>Variable</b></td>
<td width="70%"><b>Description</b></td>
</tr>
<tr>
<td><code>MONGO_URL</code><br>or<br><code>MONGODB_URI</code></td>
<td>

**Your MongoDB connection string**

- 📍 Get from [MongoDB Atlas](https://cloud.mongodb.com) or Railway MongoDB plugin
- 📝 Format: `mongodb+srv://[user]:[pass]@[host]/[db]`
- 🔌 Railway's MongoDB plugin automatically sets `MONGODB_URI`
- ✅ Application checks both variable names
- 🚫 **Never commit to Git** - set only in Railway dashboard

</td>
</tr>
</table>

---

### 🌐 Bunny CDN Configuration (Required for uploads)

<table>
<tr>
<td width="30%"><b>Variable</b></td>
<td width="70%"><b>Description</b></td>
</tr>
<tr>
<td><code>BUNNY_STORAGE_ZONE</code></td>
<td>Your Bunny.net storage zone name</td>
</tr>
<tr>
<td><code>BUNNY_ACCESS_KEY</code></td>
<td>Your Bunny.net storage zone password/key</td>
</tr>
<tr>
<td><code>BUNNY_CDN_HOST</code></td>
<td>Your CDN host URL<br>Example: <code>https://your-cdn.b-cdn.net</code></td>
</tr>
<tr>
<td><code>BUNNY_API_KEY</code><br><i>(optional)</i></td>
<td>Bunny account API key - enables automatic cache purging</td>
</tr>
</table>

---

### 📧 Email Configuration (Required for notifications)

<details open>
<summary><b>🚂 For Railway (Production)</b></summary>

<table>
<tr>
<td width="30%"><b>Variable</b></td>
<td width="70%"><b>Description</b></td>
</tr>
<tr>
<td><code>SENDGRID_API_KEY</code></td>
<td>SendGrid API key for email delivery</td>
</tr>
<tr>
<td><code>SMTP_FROM_EMAIL</code></td>
<td>Sender email address</td>
</tr>
<tr>
<td><code>SMTP_FROM_NAME</code></td>
<td>Display name (e.g., <code>"Graduation Gallery"</code>)</td>
</tr>
</table>

> **⚠️ Note:** Railway blocks SMTP ports, so SendGrid is **required** for production.

</details>

<details>
<summary><b>💻 For Localhost (Development)</b></summary>

<table>
<tr>
<td width="30%"><b>Variable</b></td>
<td width="70%"><b>Description</b></td>
</tr>
<tr>
<td><code>SMTP_HOST</code></td>
<td>SMTP server hostname (e.g., <code>smtp.gmail.com</code>)</td>
</tr>
<tr>
<td><code>SMTP_PORT</code></td>
<td>SMTP port (<code>587</code> for TLS, <code>465</code> for SSL)</td>
</tr>
<tr>
<td><code>SMTP_USERNAME</code></td>
<td>Your email address</td>
</tr>
<tr>
<td><code>SMTP_PASSWORD</code></td>
<td>Your email password or app-specific password</td>
</tr>
<tr>
<td><code>SMTP_FROM_EMAIL</code></td>
<td>Email address to send from</td>
</tr>
<tr>
<td><code>SMTP_FROM_NAME</code></td>
<td>Display name for emails</td>
</tr>
<tr>
<td><code>SMTP_ENCRYPTION</code></td>
<td>Encryption method (<code>tls</code> or <code>ssl</code>)</td>
</tr>
</table>

</details>

---

### 🛠️ How to Set Variables in Railway

<table>
<tr><td>

**Step 1:** 🌐 Go to [Railway Dashboard](https://railway.app/dashboard)

**Step 2:** 📂 Select your project

**Step 3:** ⚙️ Navigate to the **"Variables"** tab

**Step 4:** ➕ Add each environment variable

**Step 5:** 🚀 Click **"Deploy"** or wait for auto-redeploy

</td></tr>
</table>

---

## 🔧 Troubleshooting

### ❌ "Database connection error" on Login Page

<details>
<summary><b>Click to expand solution</b></summary>

**Symptoms:**
> 🔴 Red error message: *"Database connection error. Please contact the administrator or try again later."*

**✅ Solution Steps:**

1. ✔️ Check if `MONGO_URL` or `MONGODB_URI` is set in Railway dashboard
2. 🔍 Verify the MongoDB connection string is correct
3. 🧪 Test the connection string using MongoDB Compass or similar tool
4. 📋 Check Railway logs: `railway logs`
5. 🔌 If using Railway's MongoDB plugin, ensure it's linked to your service

**🔍 Common Causes:**

- ❌ Environment variable not set
- ❌ Incorrect connection string format
- ❌ MongoDB server is not accessible
- ❌ Network/firewall issues
- ❌ IP address not whitelisted in MongoDB Atlas

</details>

---

### 📦 Composer Install Fails

<details>
<summary><b>Click to expand solution</b></summary>

**Solution:**

- ✅ The `--ignore-platform-reqs` flag bypasses extension checks during installation
- ✅ Railway automatically installs the MongoDB extension
- ✅ No action needed - this is expected behavior

</details>

---

### 🗄️ MongoDB Connection Issues

<details>
<summary><b>Click to expand solution</b></summary>

**Checklist:**

- ✔️ Verify `MONGO_URL` or `MONGODB_URI` is set correctly in Railway
- ✔️ Ensure connection files use `getenv('MONGO_URL')` (not hardcoded localhost)
- ✔️ Verify MongoDB cluster allows connections from Railway's IP addresses
- ✔️ Check if database name in connection string matches your actual database
- ✔️ Test connection string format: `mongodb+srv://[user]:[pass]@[host]/[db]`

</details>

---

### 🏗️ Build Failures

<details>
<summary><b>Click to expand solution</b></summary>

**Troubleshooting Steps:**

1. 🔄 Try switching between Nixpacks and Docker builders
2. 📋 Check Railway logs for specific error messages
3. ✅ Verify all dependencies in `composer.json` are compatible
4. 🧹 Clear build cache in Railway settings
5. 🔍 Review recent commits for configuration changes

</details>

---

## 🧪 Local Testing

Test your deployment configuration locally before pushing to Railway:

```bash
# 🔍 Test MongoDB connection
php test-mongodb.php

# 🚀 Run development server
php -S localhost:8000 -t .
```

> **💡 Tip:** Use `.env` file for local environment variables instead of hardcoding.

---

## 📝 Files Updated for Deployment

All MongoDB connection files have been updated to use environment variables:

<table>
<tr>
<td width="50%">

**Configuration Files:**
- ✅ `Connection/Configuration/MongoFetch.php`
- ✅ `Connection/FetchAnnouncement.php`
- ✅ `Connection/DeleteAnnouncement.php`
- ✅ `Connection/TestAnnouncement.php`
- ✅ `Connection/SubmitAnnouncement.php`

</td>
<td width="50%">

**Admin Components:**
- ✅ `Connection/DeleteStudent.php`
- ✅ `Admin/Components/AddNewStudent.php`
- ✅ `Admin/Components/BatchUpload.php`
- ✅ `Admin/Components/StudentList.php`
- ✅ `Admin/Components/EditStudentInformation.php`

</td>
</tr>
</table>

---

<div align="center">

### 🎉 Happy Deploying!

**Need more help?** Check out the [Railway Documentation](https://docs.railway.app)

[⬆️ Back to Top](#-railway-deployment-guide)

</div>
