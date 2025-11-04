<div align="center">

# 🎓 Graduation Gallery Application

### _A Modern Digital Yearbook Platform_

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MongoDB](https://img.shields.io/badge/MongoDB-47A248?style=for-the-badge&logo=mongodb&logoColor=white)
![Railway](https://img.shields.io/badge/Railway-0B0D0E?style=for-the-badge&logo=railway&logoColor=white)

</div>

---

## 🌟 Overview

A comprehensive web platform designed for educational institutions to create, manage, and showcase digital yearbooks with **secure authentication**, **cloud storage**, and **modern media management**.

### ✨ Key Features

- 📚 **Digital Yearbook Management** - Create beautiful, interactive yearbooks
- 👥 **Student Profile System** - Comprehensive student information management
- 🖼️ **Gallery Management** - Organize and display graduation photos
- 🔐 **Secure Authentication** - Role-based access control
- ☁️ **Cloud Storage** - Powered by Bunny CDN
- 📧 **Email Notifications** - Automated email delivery system

---

## ⚙️ Environment Configuration

### 🔑 Required Environment Variables

<table>
<tr>
<td width="50%">

#### 🗄️ Database Configuration

| Variable                     | Description               |
| ---------------------------- | ------------------------- |
| `MONGO_URL` or `MONGODB_URI` | MongoDB connection string |

**Format:** `mongodb+srv://[username]:[password]@[cluster]/[database]`

</td>
<td width="50%">

#### 🌐 CDN Storage (Bunny.net)

| Variable             | Description                        |
| -------------------- | ---------------------------------- |
| `BUNNY_STORAGE_ZONE` | Storage zone name                  |
| `BUNNY_ACCESS_KEY`   | Storage zone password/key          |
| `BUNNY_CDN_HOST`     | CDN host URL                       |
| `BUNNY_API_KEY`      | _(Optional)_ Enables cache purging |

</td>
</tr>
</table>

#### 📧 Email Service Configuration

<details>
<summary><b>🚂 For Railway (Production) - Click to expand</b></summary>

| Variable           | Description                         |
| ------------------ | ----------------------------------- |
| `SENDGRID_API_KEY` | SendGrid API key for email delivery |
| `SMTP_FROM_EMAIL`  | Sender email address                |
| `SMTP_FROM_NAME`   | Sender display name                 |

> **⚠️ Note:** Railway blocks standard SMTP ports, so SendGrid is required for production.

</details>

<details>
<summary><b>💻 For Localhost (Development) - Click to expand</b></summary>

| Variable          | Description                                   |
| ----------------- | --------------------------------------------- |
| `SMTP_HOST`       | SMTP server hostname (e.g., `smtp.gmail.com`) |
| `SMTP_PORT`       | SMTP port (`587` for TLS, `465` for SSL)      |
| `SMTP_USERNAME`   | Authentication username                       |
| `SMTP_PASSWORD`   | App-specific password (for Gmail)             |
| `SMTP_FROM_EMAIL` | Sender email address                          |
| `SMTP_FROM_NAME`  | Sender display name                           |
| `SMTP_ENCRYPTION` | Encryption protocol (`tls`/`ssl`)             |

</details>

---

## 🚀 Deployment

### ☁️ Railway Platform

<table>
<tr><td>

**Step 1:** 🔗 **Connect Repository**

```
Connect your GitHub repository to Railway
```

**Step 2:** 🔧 **Configure Variables**

Set the following in Railway dashboard:

- `MONGO_URL` - Database connection
- `BUNNY_STORAGE_ZONE`, `BUNNY_ACCESS_KEY`, `BUNNY_CDN_HOST` - CDN config
- `BUNNY_API_KEY` - _(Optional)_ Cache purging
- `SENDGRID_API_KEY` - Email service
- `SMTP_FROM_EMAIL`, `SMTP_FROM_NAME` - Email sender info

**Step 3:** 🎉 **Deploy**

```
Automatic deployment with Nixpacks or Docker
```

</td></tr>
</table>

> 📖 **Need help?** Check out [`DEPLOYMENT.md`](DEPLOYMENT.md) for detailed instructions.

---

### 💻 Local Development

```bash
# 📦 Install dependencies
composer install

# 🔑 Configure environment
cp .env.example .env

# ✏️ Edit .env with your credentials

# 🚀 Start development server
php -S localhost:8000
```

---

## 📁 Project Structure

```
📂 ECADYB/
├── 🔌 Connection/     # Database operations & API endpoints
├── 👨‍💼 Admin/          # Administrative dashboard
├── 🎓 Student/        # Student portal interface
├── 🏠 LandingPage/    # Public-facing pages
└── 📚 vendor/         # PHP dependencies
```

---

## 🛠️ Technical Stack

<table>
<tr>
<td align="center" width="25%">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="48" height="48" alt="PHP"/>
<br><b>PHP 7.4+</b>
</td>
<td align="center" width="25%">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mongodb/mongodb-original.svg" width="48" height="48" alt="MongoDB"/>
<br><b>MongoDB</b>
</td>
<td align="center" width="25%">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/composer/composer-original.svg" width="48" height="48" alt="Composer"/>
<br><b>Composer</b>
</td>
<td align="center" width="25%">
<a href="https://bunny.net/cdn/" target="_blank">
<img src="https://raw.githubusercontent.com/gilbarbara/logos/main/logos/bunny.svg" width="48" height="48" alt="Bunny CDN"/>
</a>
<br><b><a href="https://bunny.net/cdn/" target="_blank">Bunny CDN</a></b>
</td>
</tr>
<tr>
<td align="center" colspan="2">
<b>📬 SendGrid API</b> (Railway)
</td>
<td align="center" colspan="2">
<b>📮 PHPMailer</b> (localhost)
</td>
</tr>
</table>

---

## 📚 Documentation

| Document                            | Description                           |
| ----------------------------------- | ------------------------------------- |
| 📘 [`DEPLOYMENT.md`](DEPLOYMENT.md) | Complete deployment guide for Railway |

---

<div align="center">

**Made with ❤️ by Team Nova Spire**

_Empowering schools to create memorable digital yearbooks_

</div>
