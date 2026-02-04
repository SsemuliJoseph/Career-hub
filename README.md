"# Career Connect Hub 🚀

> A comprehensive full-stack job application platform connecting students and graduates with employers for career opportunities, internships, and professional development.

[![PHP Version](https://img.shields.io/badge/PHP-8.x-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

---

## 📋 Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [API Documentation](#api-documentation)
- [WebSocket Server](#websocket-server)
- [Progressive Web App (PWA)](#progressive-web-app-pwa)
- [Contributing](#contributing)
- [Documentation](#documentation)
- [License](#license)

---

## 🎯 Overview

**Career Connect Hub** is a modern job application platform designed specifically for students and graduates seeking career opportunities. The platform bridges the gap between job seekers and employers with a comprehensive suite of features including job browsing, application tracking, real-time notifications, CV management, and much more.

### Why Career Connect Hub?

- **For Students**: Create profiles, browse opportunities, apply to jobs, track applications, and receive professional CV feedback
- **For Employers**: Post job listings, manage applications, review candidates, and communicate with applicants
- **For Admins**: Manage users, moderate content, review CVs, and integrate external job sources

---

## ✨ Key Features

### 🎓 Student Features
- ✅ **Comprehensive Profile Management**: Personal info, education, skills, CV upload, and profile picture
- 💼 **Job Search & Discovery**: Browse jobs/internships with smart search and filtering
- 📝 **Easy Application Process**: Apply with cover letters and CV attachments
- 📊 **Application Tracking Dashboard**: Monitor all applications in one place
- 🔔 **Real-Time Notifications**: Instant updates for new jobs and application status changes
- 📄 **CV Review Service**: Upload and request professional feedback on your CV
- 🌐 **External Job Integration**: Access jobs from JSearch and Adzuna APIs

### 🏢 Employer Features
- 📝 **Job Posting Management**: Create and manage job/internship listings
- 👥 **Applicant Review**: View applications, download CVs, and manage candidates
- 📧 **Application Notifications**: Real-time alerts when students apply
- 📈 **Hiring Pipeline**: Track and update application statuses

### 🛠️ Admin Features
- 🔐 **User Management**: Comprehensive user administration
- 📋 **CV Review Queue**: Review and provide feedback on student CVs
- 🌍 **External Job Import**: Import jobs from third-party APIs
- 📊 **Platform Analytics**: Track usage and engagement metrics

### 🌟 Platform-Wide Features
- 🔒 **Secure Authentication**: Password hashing, session management, CSRF protection
- 🔍 **Smart Search**: AJAX-powered autocomplete job search
- 🌓 **Theme Toggle**: Dark/light mode with localStorage persistence
- 📱 **Responsive Design**: Mobile-first UI with adaptive layouts
- 💨 **Progressive Web App**: Offline support and installable on devices
- ⚡ **WebSocket Integration**: Real-time bidirectional communication

---

## 🛠️ Technology Stack

### Backend
- **PHP 8.x** - Server-side logic and API endpoints
- **MySQL/MariaDB** - Relational database with mysqli
- **Composer** - PHP dependency management
- **WebSocket Server** - Real-time notifications (Ratchet)

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Custom properties, Flexbox, Grid
- **Vanilla JavaScript (ES6+)** - Modular architecture
- **Service Worker** - PWA functionality and offline caching

### External Services
- **JSearch API (RapidAPI)** - Job listings integration
- **Adzuna API** - Additional job data source

### Development Tools
- **Git** - Version control
- **npm** - Frontend package management (optional)
- **PHPDotEnv** - Environment configuration

---

## 📦 Prerequisites

Before you begin, ensure you have the following installed:

- **PHP** >= 8.0
- **MySQL** or **MariaDB** >= 8.0
- **Apache** or **Nginx** web server
- **Composer** (for PHP dependencies)
- **Git** (for version control)

Optional but recommended:
- **Node.js** and **npm** (for frontend tooling)
- **OpenSSL** (for HTTPS/WSS in production)

---

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/SsemuliJoseph/Career-hub.git
cd Career-hub
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Database Setup

Create a MySQL database and import the schema:

```bash
mysql -u your_username -p
```

```sql
CREATE DATABASE career_hub;
USE career_hub;
SOURCE uniconnect_db.sql;
```

### 4. Configure Environment Variables

Create a `.env` file in the root directory:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```env
DB_HOST=localhost
DB_NAME=career_hub
DB_USER=your_username
DB_PASS=your_password
```

### 5. Set Up File Permissions

Create required directories and set permissions:

```bash
mkdir -p cache/api cache/notifications uploads
chmod 755 cache cache/api cache/notifications uploads
```

### 6. Configure Web Server

#### Apache (with mod_rewrite)

Ensure `.htaccess` is in the root directory and `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Point your virtual host to the project root directory.

#### Nginx

Add this to your Nginx configuration:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
}
```

---

## ⚙️ Configuration

### API Keys Setup

1. **Adzuna API** (Recommended - Free):
   - Sign up at [https://developer.adzuna.com/](https://developer.adzuna.com/)
   - Get your App ID and App Key
   - Update in `classes/ExternalAPIService.php` (lines 12-13)

2. **JSearch API** (Alternative - Free):
   - Sign up at [https://rapidapi.com/](https://rapidapi.com/)
   - Subscribe to JSearch API free tier
   - Update in `classes/ExternalAPIService.php` (line 84)

### Internal API Tokens

Generate secure tokens for internal APIs:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

Update tokens in these files:
- `api/v1/export_jobs.php`
- `api/v1/export_applications.php`
- `api/v1/import_jobs.php`
- `api/v1/stats.php`

---

## 🎮 Usage

### Starting the Application

1. **Start your web server** (Apache/Nginx)
2. **Access the application**: `http://localhost/Career-hub`
3. **Create an admin account** by registering and manually updating the role in the database

### Starting the WebSocket Server

For real-time notifications:

**Linux/Mac:**
```bash
php websocket_server.php
```

**Windows:**
```bash
start_websocket.bat
```

Or run in background:
```bash
nohup php websocket_server.php > /dev/null 2>&1 &
```

### Testing the Setup

Visit `http://localhost/Career-hub/test_setup.php` to verify:
- ✅ Database connection
- ✅ File permissions
- ✅ PHP extensions
- ✅ Cache directories

---

## 📁 Project Structure

```
Career-hub/
├── api/                      # REST API endpoints
│   ├── v1/                   # API version 1
│   ├── applications.php      # Application management
│   ├── get_jobs.php         # Job retrieval
│   └── ...
├── assets/                   # Static assets (images, videos)
├── cache/                    # Cache storage
│   ├── api/                 # API response cache
│   └── notifications/       # Notification cache
├── classes/                  # PHP OOP classes
│   ├── Database.php         # Database singleton
│   ├── User.php             # User model
│   ├── Job.php              # Job model
│   ├── Application.php      # Application model
│   └── ExternalAPIService.php
├── components/               # Reusable components
├── config/                   # Configuration files
├── css/                      # Stylesheets
│   ├── global.css           # Global styles
│   ├── responsive.css       # Responsive design
│   └── ...
├── includes/                 # Shared PHP includes
│   ├── session.php          # Session management
│   ├── db.php               # Database connection
│   ├── helpers.php          # Utility functions
│   └── navbar.php           # Navigation bar
├── js/                       # JavaScript modules
│   ├── api.js               # API wrapper
│   ├── app.js               # PWA functionality
│   ├── theme.js             # Theme management
│   ├── websocket-client.js  # WebSocket client
│   └── ...
├── pages/                    # Application pages
│   ├── login.php            # Login page
│   ├── signup.php           # Registration
│   ├── student.php          # Student dashboard
│   ├── employer.php         # Employer dashboard
│   └── ...
├── sql/                      # SQL scripts
├── uploads/                  # User uploads (CVs, images)
├── vendor/                   # Composer dependencies
├── index.php                 # Landing page
├── service-worker.js         # PWA service worker
├── websocket_server.php      # WebSocket server
├── composer.json             # PHP dependencies
└── uniconnect_db.sql         # Database schema
```

---

## 🔌 API Documentation

The platform provides RESTful APIs for various operations:

### Authentication
- `POST /api/login.php` - User login
- `POST /api/signup.php` - User registration
- `POST /api/logout.php` - User logout

### Jobs
- `GET /api/get_jobs.php` - Retrieve job listings
- `GET /api/search_jobs.php` - Search jobs with filters
- `POST /api/post_job.php` - Create new job posting
- `DELETE /api/delete_job.php` - Remove job posting

### Applications
- `GET /api/applications.php` - Get user applications
- `POST /api/apply.php` - Submit job application
- `PUT /api/update_application_status.php` - Update status

### External Integration
- `GET /api/v1/export_jobs.php` - Export jobs (requires API token)
- `POST /api/v1/import_jobs.php` - Import external jobs

For detailed API documentation, see [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

---

## 🔌 WebSocket Server

The application includes real-time features via WebSockets:

### Starting the Server

```bash
php websocket_server.php
```

Default configuration:
- **Host**: 0.0.0.0
- **Port**: 8080

### Features
- Real-time notifications for new jobs
- Application status updates
- Live user activity tracking

### Testing WebSockets

Open `test_websocket.html` in your browser to test the connection.

For detailed testing guide, see [WEBSOCKET_TESTING.md](WEBSOCKET_TESTING.md)

---

## 📱 Progressive Web App (PWA)

The platform is a fully functional PWA:

### Features
- ✅ **Installable**: Add to home screen on mobile/desktop
- ✅ **Offline Support**: Browse cached content without internet
- ✅ **Service Worker**: Background sync and notifications
- ✅ **Manifest**: App metadata and icons

### Testing PWA

1. Visit the site in Chrome/Edge
2. Look for the install prompt
3. Open DevTools > Application > Service Workers
4. Verify registration and caching

---

## 🤝 Contributing

We welcome contributions! Here's how you can help:

### Getting Started

1. **Fork the repository**
2. **Create a feature branch**
   ```bash
   git checkout -b feature/amazing-feature
   ```
3. **Make your changes**
4. **Commit with clear messages**
   ```bash
   git commit -m "Add: Amazing new feature"
   ```
5. **Push to your fork**
   ```bash
   git push origin feature/amazing-feature
   ```
6. **Open a Pull Request**

### Code Style

- Follow PSR-12 for PHP code
- Use ESLint for JavaScript (if configured)
- Write clear, descriptive commit messages
- Add comments for complex logic
- Test your changes thoroughly

### Reporting Issues

Found a bug? Please [open an issue](https://github.com/SsemuliJoseph/Career-hub/issues) with:
- Clear description
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (if applicable)

---

## 📚 Documentation

Comprehensive documentation is available:

- **[GETTING_STARTED.md](GETTING_STARTED.md)** - Quick start guide (10 minutes)
- **[PROJECT_README.md](PROJECT_README.md)** - Complete project guide
- **[PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md)** - Learning documentation
- **[API_DOCUMENTATION.md](API_DOCUMENTATION.md)** - API reference
- **[PROJECT_ARCHITECTURE.md](PROJECT_ARCHITECTURE.md)** - Architecture overview
- **[DISTRIBUTED_SETUP_GUIDE.md](DISTRIBUTED_SETUP_GUIDE.md)** - Multi-server setup
- **[SESSION_SECURITY.md](SESSION_SECURITY.md)** - Security best practices
- **[WEBSOCKET_TESTING.md](WEBSOCKET_TESTING.md)** - WebSocket testing guide
- **[HTML_LEARNING_GUIDE.md](HTML_LEARNING_GUIDE.md)** - HTML concepts
- **[CSS_LEARNING_GUIDE.md](CSS_LEARNING_GUIDE.md)** - CSS concepts
- **[JS_LEARNING_GUIDE.md](JS_LEARNING_GUIDE.md)** - JavaScript concepts

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👥 Authors

- **Joseph Ssemuli** - [SsemuliJoseph](https://github.com/SsemuliJoseph)

---

## 🙏 Acknowledgments

- PHP community for excellent documentation
- Ratchet for WebSocket support
- Contributors and testers
- All students and educators using this platform

---

## 📞 Support

Need help? Here's how to get support:

- 📧 **Email**: [Contact via GitHub](https://github.com/SsemuliJoseph)
- 🐛 **Issues**: [GitHub Issues](https://github.com/SsemuliJoseph/Career-hub/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/SsemuliJoseph/Career-hub/discussions)

---

## 🚀 Quick Links

- [Live Demo](#) (if available)
- [Documentation](#documentation)
- [Report Bug](https://github.com/SsemuliJoseph/Career-hub/issues)
- [Request Feature](https://github.com/SsemuliJoseph/Career-hub/issues)

---

<div align="center">

**Made with ❤️ for students and educators**

⭐ Star this repo if you find it helpful!

</div>" 
