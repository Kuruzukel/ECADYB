<div align="center">

<p align="center">
  <img src="img/PREVIEWLOGO.png" alt="Graduation Gallery Logo" width="200">
</p>

# Exact Colleges of Asia

## Graduation Gallery Application

### A comprehensive, professional-grade digital yearbook platform designed for seamless management of student profiles, media galleries, and yearbook publications across multiple user roles.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MongoDB](https://img.shields.io/badge/MongoDB-47A248?style=for-the-badge&logo=mongodb&logoColor=white)
![Railway](https://img.shields.io/badge/Railway-0B0D0E?style=for-the-badge&logo=railway&logoColor=white)

</div>

---

## Quick Navigation

| Getting Started | [View](#getting-started) |
| Development | [View](#development) |
| Documentation | [View](#documentation) |

---

## Overview

Graduation Gallery is an enterprise-level digital yearbook management platform that combines administrative dashboards, student portals, and public information systems. Built with modern web technologies and cloud infrastructure, it provides a seamless experience for administrators, students, and visitors to create, manage, and showcase memorable graduation moments.

---

## Key Capabilities

### Administration & Management

- **Multi-role Administration** — Admin and Student interfaces with role-based access control
- **Student Management** — Complete lifecycle from registration to graduation
- **Media Management** — Batch upload, categorization, and CDN-backed storage
- **Yearbook Publishing** — Interactive flipbook-style digital yearbooks

### Content & Publishing

- **Department Yearbooks** — Dedicated yearbook views for 7 academic departments
- **Photo Galleries** — High-resolution image galleries with advanced organization
- **Announcements System** — Real-time announcements and notifications
- **Event Calendar** — Schedule and track important academic events

### Security & Infrastructure

- **Secure Authentication** — JWT-based authentication with session management
- **Cloud Storage** — Bunny.net CDN integration for global content delivery
- **Email Service** — Automated email notifications via SendGrid/SMTP
- **Responsive Design** — Works seamlessly on all devices

---

## Features

### Admin Dashboard

Comprehensive administrative interface with real-time management capabilities

- Real-time student analytics
- Batch student upload with CSV templates
- Media gallery management
- Yearbook cover management
- Department administration
- Announcement creation
- Event calendar management
- Active session monitoring
- User account management
- Theme customization
- Profile management
- Password management

### Landing Page

Professional public-facing website

- Hero section with call-to-action
- About section
- Department showcase
- Responsive design
- SEO optimized
- Social media integration

### Authentication System

Secure user authentication with complete workflow

- Secure login & registration
- Password recovery system
- Email verification
- Session management
- JWT token authentication
- Account settings

### Student Dashboard

Student portal for yearbook access and profile management

- Personal dashboard
- Department yearbook viewer
- Interactive flipbook reader
- Profile management
- Password management
- Announcement viewing
- Responsive interface

---

## Tech Stack

### Frontend Technologies

| Technology | Purpose                           | Version |
| ---------- | --------------------------------- | ------- |
| HTML5      | Semantic markup & structure       | Latest  |
| CSS3       | Styling & responsive design       | Latest  |
| JavaScript | Client-side logic & interactivity | ES6+    |
| jQuery     | DOM manipulation & utilities      | 3.x     |
| Turn.js    | Flipbook page-turning effects     | Latest  |
| Bootstrap  | UI framework & components         | 4.x/5.x |

### Backend Technologies

| Technology | Purpose                         | Version |
| ---------- | ------------------------------- | ------- |
| PHP        | Server-side programming         | 7.4+    |
| MongoDB    | NoSQL database                  | Latest  |
| Composer   | PHP dependency management       | Latest  |
| PHPMailer  | Email functionality (localhost) | Latest  |
| SendGrid   | Email API (production)          | Latest  |
| JWT        | Token-based authentication      | Latest  |

### Infrastructure & DevOps

| Tool          | Purpose                           |
| ------------- | --------------------------------- |
| Bunny.net CDN | Global content delivery & storage |
| Railway       | Cloud hosting & deployment        |
| Docker        | Containerization                  |
| Nixpacks      | Build system                      |
| Apache        | Web server                        |

### Browser Support

| Browser         | Status    |
| --------------- | --------- |
| Chrome          | Latest    |
| Firefox         | Latest    |
| Safari          | Latest    |
| Edge            | Latest    |
| Mobile Browsers | Supported |

---

## Project Structure

```
ECADYB/
│
├── Admin/                           # Administrative Dashboard
│   ├── assets/
│   │   ├── css/                     # 11 CSS files
│   │   │   ├── AddNewStudent.css
│   │   │   ├── AdminDashboard.css
│   │   │   ├── BatchTemplates.css
│   │   │   ├── BatchUpload.css
│   │   │   ├── ChangePassword.css
│   │   │   ├── CreateAnnouncement.css
│   │   │   ├── EventCalendar.css
│   │   │   ├── StudentList.css
│   │   │   ├── Themes.css
│   │   │   └── UploadBox.css
│   │   └── js/                      # 10 JavaScript files
│   │       ├── AddNewStudent.js
│   │       ├── AdminDashboard.js
│   │       ├── BatchTemplates.js
│   │       ├── BatchUpload.js
│   │       ├── ChangePassword.js
│   │       ├── CreateAnnouncement.js
│   │       ├── EventCalendar.js
│   │       ├── SessionTracker.js
│   │       ├── StudentList.js
│   │       └── Themes.js
│   ├── Components/                  # 12 PHP pages
│   │   ├── ActiveSessions.php
│   │   ├── AddNewStudent.php
│   │   ├── AdminDashboard.php
│   │   ├── AdminLogout.php
│   │   ├── BatchTemplates.php
│   │   ├── BatchUpload.php
│   │   ├── ChangePassword.php
│   │   ├── CreateAnnouncement.php
│   │   ├── EventCalendar.php
│   │   ├── FixPlaceholderStudentIDs.php
│   │   ├── StudentList.php
│   │   └── Themes.php
│   ├── Departments/                 # Department Yearbooks
│   │   ├── assets/
│   │   │   ├── css/                 # 8 CSS files
│   │   │   └── js/                  # 8 JavaScript files
│   │   ├── BusinessAdministration.php
│   │   ├── Criminology.php
│   │   ├── Education.php
│   │   ├── InformationSystem.php
│   │   ├── Maritime.php
│   │   ├── Nursing.php
│   │   └── Tourism.php
│   ├── Flipbook/                    # Turn.js library
│   │   └── turn.js/
│   ├── Yearbook/                    # Admin yearbook viewer
│   │   ├── css/
│   │   ├── js/
│   │   ├── pics/
│   │   ├── FetchCoverData.php
│   │   └── index.html
│   ├── index.php                    # Admin entry point
│   └── test-admin-profile.php
│
├── Student/                         # Student Portal
│   ├── assets/
│   │   ├── css/                     # Student stylesheets
│   │   └── js/                      # Student JavaScript
│   ├── Components/                  # Student pages
│   │   ├── About.php
│   │   ├── ChangePassword.php
│   │   ├── Index.html
│   │   ├── StudentDashboard.php
│   │   ├── StudentLogout.php
│   │   └── Yearbook.php
│   ├── Yearbook/                    # Student yearbook viewer
│   │   ├── css/
│   │   ├── js/
│   │   ├── pics/
│   │   └── index.html
│   └── index.php                    # Student entry point
│
├── Public/                          # Public Authentication
│   ├── assets/
│   │   ├── css/                     # Auth stylesheets
│   │   └── js/                      # Auth JavaScript
│   └── Components/
│       ├── ChangePassword.html
│       ├── ForgotPassword.html
│       ├── Loader.html
│       └── Login.php
│
├── LandingPage/                     # Main Landing Page
│   ├── index.html                   # Landing page
│   ├── style.css                    # Landing styles
│   ├── script.js                    # Landing functionality
│   └── service-worker.js            # PWA service worker
│
├── Connection/                      # Backend API & Database
│   ├── Admin/                       # Admin operations
│   │   ├── AddActiveSessionsField.php
│   │   └── ChangePassword.php
│   ├── Announcement/                # Announcement CRUD
│   │   ├── DeleteAnnouncement.php
│   │   ├── FetchAnnouncement.php
│   │   ├── FetchAnnouncements.php
│   │   └── SubmitAnnouncement.php
│   ├── Configuration/               # Database config & utilities
│   │   ├── config.php
│   │   ├── MongoConnect.php
│   │   ├── MongoFetch.php
│   │   ├── BunnyConfig.php
│   │   ├── EmailConfig.php
│   │   ├── JWTConfig.php
│   │   └── [30+ utility files]
│   ├── Cover/                       # Yearbook cover management
│   │   ├── DeleteCover.php
│   │   ├── FetchCovers.php
│   │   └── UploadCover.php
│   ├── Logo/                        # Logo management
│   │   ├── DeleteLogo.php
│   │   ├── FetchAdminLogo.php
│   │   └── UploadLogo.php
│   ├── Photos/                      # Photo gallery operations
│   ├── Session/                     # Session management
│   └── Student/                     # Student data operations
│
├── img/                             # Shared Assets Directory
│   ├── BATCH TEMPLATES/             # CSV templates for batch upload
│   ├── CAROUSEL/                    # Carousel images
│   ├── SampleLogos/                 # Sample logo files
│   ├── YB COVER/                    # Yearbook cover images
│   ├── YB COVER B-F/                # Yearbook back/front covers
│   └── [50+ image files]
│
├── Turn.js/                         # Turn.js Library
│   ├── extras/
│   └── lib/
│
├── docker/                          # Docker Configuration
│   ├── apache.conf
│   └── php.ini
│
├── conf/                            # Server Configuration
│   └── httpd.conf
│
├── vendor/                          # PHP Dependencies (Composer)
│   ├── composer/
│   ├── mongodb/
│   ├── phpmailer/
│   ├── sendgrid/
│   ├── firebase/
│   └── [other dependencies]
│
├── .env                             # Environment variables (not in repo)
├── .env.example                     # Environment template
├── composer.json                    # PHP dependencies
├── composer.lock                    # Locked dependency versions
├── index.php                        # Application entry point
├── Dockerfile                       # Docker container definition
├── docker-entrypoint.sh             # Docker startup script
├── nixpacks.toml                    # Nixpacks build configuration
├── railway.json                     # Railway deployment config
├── .htaccess                        # Apache rewrite rules
├── .railwayignore                   # Railway ignore patterns
├── README.md                        # Project documentation
├── DEPLOYMENT.md                    # Deployment guide
└── SECURITY.md                      # Security policies
```

---

## Applications

### 1. Admin Dashboard

Professional administration interface for managing all aspects of the digital yearbook platform.

**Pages:** 12+ | **CSS Files:** 11 | **JS Files:** 10 | **Features:** Dashboard, Student Management, Batch Upload, Media Gallery, Yearbook Covers, Announcements, Event Calendar, Department Management, Active Sessions, Themes, Profile, Password Management

[Full Documentation](#admin-dashboard)

### 2. Student Portal

Student dashboard for yearbook viewing and profile management.

**Pages:** 6+ | **CSS Files:** Multiple | **JS Files:** Multiple | **Features:** Dashboard, Department Yearbooks, Interactive Flipbook, About Section, Profile Management, Password Management

[Full Documentation](#student-portal)

### 3. Authentication System

Secure authentication with complete user workflow.

**Pages:** 4+ | **CSS Files:** Multiple | **JS Files:** Multiple | **Features:** Login, Password Recovery, Account Settings, Session Management

[Full Documentation](#authentication-system)

### 4. Landing Page

Professional public-facing website showcasing the platform.

**Pages:** 1 | **CSS Files:** 1 | **JS Files:** 2 | **Features:** Hero Section, About, Departments, Responsive Design, PWA Support

[Full Documentation](#landing-page)

### 5. Backend API

RESTful API built with PHP and MongoDB for data management.

**Technology:** PHP 7.4+ | **Database:** MongoDB | **Architecture:** REST API | **Features:** Student Management, Photo Management, Announcement System, Cover Management, Session Management, Email Service, CDN Integration

[Full Documentation](#backend-api)

---

## Getting Started

### Prerequisites

```bash
# Frontend Requirements
- Modern web browser (Chrome, Firefox, Safari, Edge)
- Internet connection for CDN resources

# Backend Requirements
- PHP 7.4 or higher
- MongoDB 4.0 or higher
- Composer
- Apache/Nginx web server
- MongoDB PHP Extension

# Production Requirements
- Railway account (for deployment)
- MongoDB Atlas account (for database)
- Bunny.net account (for CDN storage)
- SendGrid account (for email service)
```

### Installation

#### Local Development Setup

```bash
# 1. Clone the repository
git clone https://github.com/Kuruzu28/ECADYB.git
cd ECADYB

# 2. Install PHP dependencies
composer install

# 3. Configure environment
cp .env.example .env
# Edit .env file with your credentials

# 4. Start MongoDB service
# Windows: net start MongoDB
# Linux/Mac: sudo systemctl start mongod

# 5. Start development server
php -S localhost:8000

# 6. Access the application
# Landing Page: http://localhost:8000
# Admin: http://localhost:8000/Admin
# Student: http://localhost:8000/Student
# Login: http://localhost:8000/Public/Components/Login.php
```

#### Environment Configuration

Create a `.env` file in the root directory:

```env
# Database Configuration
MONGO_URL=mongodb+srv://username:password@cluster.mongodb.net/database
# or
MONGODB_URI=mongodb+srv://username:password@cluster.mongodb.net/database

# Bunny.net CDN Configuration
BUNNY_STORAGE_ZONE=your-storage-zone
BUNNY_ACCESS_KEY=your-access-key
BUNNY_CDN_HOST=https://your-cdn-host.b-cdn.net
BUNNY_API_KEY=your-api-key  # Optional, for cache purging

# Email Configuration (Production - Railway)
SENDGRID_API_KEY=your-sendgrid-api-key
SMTP_FROM_EMAIL=noreply@yourdomain.com
SMTP_FROM_NAME=Graduation Gallery

# Email Configuration (Development - Localhost)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_FROM_EMAIL=your-email@gmail.com
SMTP_FROM_NAME=Graduation Gallery
SMTP_ENCRYPTION=tls
```

### Quick Start Commands

```bash
# Development
php -S localhost:8000          # Start local server

# Database
composer dump-autoload         # Regenerate autoload files

# Production
# Deploy to Railway (automatic via GitHub integration)
```

---

## Development

### Project Architecture

The application follows a modular MVC-inspired structure:

```
Application/
├── Components/           # PHP pages (views)
├── assets/
│   ├── css/             # Stylesheets (page-specific)
│   ├── js/              # JavaScript (page-specific)
│   └── images/          # Local images
├── Connection/          # Backend logic (controllers)
└── index.php           # Entry point
```

### Development Guidelines

#### Code Organization

- Keep page-specific CSS and JS in separate files
- Use kebab-case for file names
- Organize pages by feature/module
- Follow consistent naming conventions

#### Asset Management

- Use centralized `/img/` folder for shared assets
- Reference images with relative paths
- Optimize images before adding to repository
- Use Bunny.net CDN for production assets

#### Styling

- Use consistent CSS naming conventions
- Keep custom CSS modular and page-specific
- Ensure responsive design for all screen sizes
- Test across multiple browsers

#### JavaScript

- Use vanilla JavaScript or jQuery
- Maintain separate files for each major feature
- Include proper error handling and validation
- Comment complex logic

#### PHP Backend

- Follow PSR-4 autoloading standards
- Use prepared statements for database queries
- Implement proper error handling
- Validate and sanitize all inputs

---

## Deployment

### Railway Platform Deployment

#### Step 1: Connect Repository

1. Sign up for [Railway](https://railway.app)
2. Create a new project
3. Connect your GitHub repository

#### Step 2: Configure Environment Variables

Set the following in Railway dashboard:

**Database:**

- `MONGO_URL` or `MONGODB_URI`

**CDN Storage:**

- `BUNNY_STORAGE_ZONE`
- `BUNNY_ACCESS_KEY`
- `BUNNY_CDN_HOST`
- `BUNNY_API_KEY` (optional)

**Email Service:**

- `SENDGRID_API_KEY`
- `SMTP_FROM_EMAIL`
- `SMTP_FROM_NAME`

#### Step 3: Deploy

Railway will automatically:

- Detect the PHP application
- Install dependencies via Composer
- Build using Nixpacks or Docker
- Deploy to production

#### Step 4: Configure Domain

1. Add custom domain in Railway settings
2. Update DNS records
3. SSL certificate is automatically provisioned

For detailed deployment instructions, see [DEPLOYMENT.md](DEPLOYMENT.md)

---

## Documentation

### Main Documentation

- **README.md** — Project overview (this file)
- **DEPLOYMENT.md** — Complete deployment guide for Railway
- **SECURITY.md** — Security policies and best practices

### Application Documentation

Each major component has its own documentation:

- Admin Dashboard — Comprehensive admin guide
- Student Portal — Student interface guide
- Authentication System — Auth workflow documentation
- Backend API — API endpoints and usage

---

## Project Statistics

| Metric              | Count                                    |
| ------------------- | ---------------------------------------- |
| Total Pages         | 40+ HTML/PHP pages                       |
| Applications        | 5 (Admin, Student, Public, Landing, API) |
| CSS Files           | 30+ stylesheets                          |
| JavaScript Files    | 30+ scripts                              |
| PHP Backend Files   | 100+ files                               |
| Departments         | 7 academic departments                   |
| Shared Assets       | 50+ files                                |
| Documentation Files | 3 README files                           |
| Database            | MongoDB (NoSQL)                          |

---

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Code Standards

- Follow existing code style
- Write meaningful commit messages
- Update documentation as needed
- Test changes before submitting PR
- Ensure responsive design compatibility

---

## License

This project is licensed under the MIT License - see the LICENSE file for details.

---

## Contact & Support

**Developer:** Team Nova Spire  
**GitHub:** [@Kuruzu28](https://github.com/Kuruzu28)  
**Institution:** Exact Colleges of Asia  
**Location:** Philippines

For issues, questions, or suggestions:

- Check the [Documentation](#documentation) section
- Review application-specific guides
- Open an issue on GitHub

---

<div align="center">

## ECADYB

**Exact Colleges of Asia - Digital Yearbook**

_Empowering schools to create memorable digital yearbooks_

© 2026 Graduation Gallery. All Rights Reserved.

</div>
