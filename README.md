# Career Hub

> A modern web platform that connects students and graduates with internships, part-time jobs, graduate trainee programs, and full-time employment opportunities.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql)
![Apache](https://img.shields.io/badge/Apache-Web%20Server-D22128?style=for-the-badge&logo=apache)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

---

# Overview

Career Hub is a full-stack web application designed to bridge the gap between students, recent graduates, and employers. The platform enables students to discover internship opportunities, graduate trainee programs, scholarships, and entry-level jobs while giving employers an efficient way to advertise vacancies and manage applications.

The project was developed as a practical solution to improve access to career opportunities for university students and fresh graduates.

---

# Features

## Student Features

- User registration and login
- Secure authentication
- Student profile management
- Upload CV/Resume
- Browse available jobs
- Browse internship opportunities
- Search and filter vacancies
- Apply for jobs online
- View submitted applications
- Save favourite jobs
- Dashboard showing application history

---

## Employer Features

- Employer registration
- Company profile management
- Post new jobs
- Edit job postings
- Delete expired vacancies
- View applicants
- Manage job listings
- Review student profiles

---

## Administrator Features

- Secure admin dashboard
- Manage students
- Manage employers
- Approve or remove job postings
- Monitor platform activity
- Manage categories
- Manage announcements
- Generate platform statistics

---

# Technologies Used

## Frontend

- HTML5
- CSS3
- JavaScript
- Bootstrap

## Backend

- PHP

## Database

- MySQL

## Server

- Apache

## Development Tools

- Visual Studio Code
- XAMPP / WAMP / LAMP
- Git
- GitHub

---

# Project Structure

```
Career-Hub/
│
├── admin/
│   ├── dashboard
│   ├── manage-users
│   ├── manage-jobs
│   └── reports
│
├── employer/
│   ├── dashboard
│   ├── post-job
│   ├── applicants
│   └── company-profile
│
├── student/
│   ├── dashboard
│   ├── applications
│   ├── profile
│   └── saved-jobs
│
├── assets/
│   ├── css
│   ├── js
│   └── images
│
├── database/
│   └── career_hub.sql
│
├── includes/
│
├── uploads/
│
├── index.php
├── login.php
├── register.php
└── README.md
```

---

# System Architecture

```
+--------------------+
|    Web Browser     |
+---------+----------+
          |
          |
          ▼
+--------------------+
|     PHP Backend    |
+---------+----------+
          |
          |
          ▼
+--------------------+
|   MySQL Database   |
+--------------------+
```

---

# Database Modules

The system manages information using multiple relational tables, including:

- Users
- Students
- Employers
- Jobs
- Applications
- Categories
- Saved Jobs
- Notifications
- Admin

---

# Installation

## 1. Clone the repository

```bash
git clone https://github.com/SsemuliJoseph/Career-hub.git
```

---

## 2. Navigate into the project

```bash
cd Career-hub
```

---

## 3. Move the project

Copy the project folder into your web server directory.

Example (XAMPP):

```
htdocs/Career-hub
```

Example (LAMP):

```
/var/www/html/Career-hub
```

---

## 4. Create the database

Open phpMyAdmin and create a database named:

```
career_hub
```

---

## 5. Import SQL

Import

```
database/career_hub.sql
```

---

## 6. Configure database connection

Update your database credentials.

Example:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "career_hub";
```

---

## 7. Start the server

Start

- Apache
- MySQL

Visit

```
http://localhost/Career-hub
```

---

# Future Improvements

- Email verification
- Password recovery
- AI-powered job recommendations
- Resume analysis
- CV scoring
- Chat between employers and applicants
- Company verification
- Google authentication
- LinkedIn authentication
- Mobile responsive redesign
- REST API
- Notification system
- Interview scheduling
- Resume builder
- Certificate verification
- Recommendation engine

---

# Security Features

- Password hashing
- Session authentication
- Input validation
- SQL injection protection
- XSS prevention
- Authentication middleware
- Role-based access control

---

# Learning Outcomes

This project strengthened practical experience in:

- Full-stack web development
- PHP programming
- Database design
- Authentication systems
- CRUD operations
- MVC concepts
- Session management
- Git and GitHub workflow
- Responsive web design
- Software engineering best practices

---

# Contributing

Contributions are welcome.

1. Fork the repository

2. Create a feature branch

```bash
git checkout -b feature/new-feature
```

3. Commit your changes

```bash
git commit -m "Added new feature"
```

4. Push

```bash
git push origin feature/new-feature
```

5. Open a Pull Request

---

# Author

## Joseph Ssemuli

Computer Science Student

Mbarara University of Science and Technology (MUST)

GitHub:

https://github.com/SsemuliJoseph

LinkedIn:

www.linkedin.com/in/joseph-ssemuli-955048344

---

# License

This project is licensed under the MIT License.

---

## Support

If you found this project useful, please consider giving it a ⭐ on GitHub.
