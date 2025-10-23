# 🌿 GreenLife Wellness Center

<div align="center">

![GreenLife Logo](images/logo.png)

**Holistic Wellness Center in Colombo, Sri Lanka**

*Reconnect with Nature & Your Inner Peace*

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26-red.svg)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6-blue.svg)](https://developer.mozilla.org/en-US/docs/Web/CSS)

</div>

---

## 📋 Table of Contents

- [🌟 Overview](#-overview)
- [✨ Features](#-features)
- [🏗️ Architecture](#️-architecture)
- [🚀 Getting Started](#-getting-started)
- [📁 Project Structure](#-project-structure)
- [👥 User Roles](#-user-roles)
- [🛠️ Technologies Used](#️-technologies-used)
- [📊 Database Schema](#-database-schema)
- [🎨 Design Features](#-design-features)
- [📱 Screenshots](#-screenshots)
- [🔧 Configuration](#-configuration)
- [📝 API Endpoints](#-api-endpoints)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)
- [📞 Contact](#-contact)

---

## 🌟 Overview

GreenLife Wellness Center is a comprehensive web-based wellness management system designed to provide holistic health services. Located in Colombo, Sri Lanka, it combines ancient healing wisdom with modern technology to offer personalized wellness experiences.

### 🎯 Mission
To empower individuals with holistic wellness support for lifelong well-being through personalized, integrative care—naturally and compassionately.

### 🌍 Vision
A world where every person has access to personalized, integrative care—naturally and compassionately.

---

## ✨ Features

### 🏠 **Public Features**
- **Responsive Homepage** with hero section and wellness programs
- **Service Catalog** with detailed descriptions and booking options
- **Therapist Directory** with profiles and specializations
- **Resource Library** with articles, videos, and health tips
- **Contact System** with inquiry management
- **About Page** with company story and mission

### 👤 **Client Dashboard**
- **Profile Management** with progress tracking
- **Appointment Booking** with therapist selection
- **Session History** and progress monitoring
- **Resource Access** to articles and videos
- **Inquiry Submission** and response tracking

### 👨‍⚕️ **Therapist Dashboard**
- **Client Management** with assigned clients
- **Appointment Scheduling** and management
- **Session Notes** and progress tracking
- **Inquiry Response** system
- **Availability Management**

### 👨‍💼 **Admin Dashboard**
- **User Management** (clients, therapists, admins)
- **Service Management** with CRUD operations
- **Resource Management** (articles, videos, tips)
- **Appointment Oversight** and analytics
- **System Configuration**

### 🎨 **Design Features**
- **Modern UI/UX** with clean, intuitive design
- **Responsive Layout** for all devices
- **Interactive Elements** with smooth animations
- **Accessibility Features** with ARIA labels
- **Color-coded Categories** for easy navigation

---

## 🏗️ Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   Database      │
│                 │    │                 │    │                 │
│ • HTML5         │◄──►│ • PHP 7.4+      │◄──►│ • MySQL 8.0+    │
│ • CSS3          │    │ • Session Mgmt  │    │ • 15+ Tables    │
│ • JavaScript    │    │ • File Upload   │    │ • Relationships │
│ • Font Awesome  │    │ • Security      │    │ • Sample Data   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

---

## 🚀 Getting Started

### Prerequisites
- **Web Server**: Apache/Nginx
- **PHP**: Version 7.4 or higher
- **MySQL**: Version 8.0 or higher
- **Web Browser**: Modern browser with JavaScript enabled

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/greenlife-wellness.git
   cd greenlife-wellness
   ```

2. **Database Setup**
   ```bash
   # Import the database schema
   mysql -u root -p < greenlife_wellness.sql
   ```

3. **Configuration**
   ```php
   // Update php/dbconnect.php with your database credentials
   $host = "localhost";
   $user = "your_username";
   $pass = "your_password";
   $dbname = "greenlife_wellness";
   ```

4. **File Permissions**
   ```bash
   # Set proper permissions for uploads directory
   chmod 755 images/uploads/
   chmod 755 images/articles/
   chmod 755 images/videos/
   ```

5. **Web Server Configuration**
   - Point your web server document root to the project directory
   - Ensure PHP and MySQL extensions are enabled
   - Configure URL rewriting if needed

6. **Access the Application**
   - Open your browser and navigate to your server URL
   - Default admin credentials: `admin` / `123`
   - Default therapist credentials: `therapist` / `123`
   - Default client credentials: `client` / `123`

---

## 📁 Project Structure

```
GreenLife/
├── 📁 css/                    # Stylesheets
│   ├── index.css             # Homepage styles
│   ├── dashboard.css         # Dashboard styles
│   ├── services.css          # Services page styles
│   ├── login.css             # Authentication styles
│   └── ...                   # Other page-specific styles
├── 📁 html/                   # Static HTML pages
│   ├── index.html            # Homepage
│   ├── about.html            # About page
│   ├── contact.html          # Contact page
│   ├── login.html            # Login page
│   └── appointment.html      # Appointment booking
├── 📁 php/                    # Backend PHP scripts
│   ├── dbconnect.php         # Database connection
│   ├── login.php             # Authentication
│   ├── admin_dashboard.php   # Admin panel
│   ├── client_dashboard.php  # Client dashboard
│   ├── therapist_dashboard.php # Therapist dashboard
│   ├── services.php          # Services management
│   ├── therapists.php        # Therapist management
│   ├── resources.php         # Resource library
│   ├── add_*.php             # CRUD operations
│   ├── delete_*.php          # Delete operations
│   └── ...                   # Other backend scripts
├── 📁 images/                 # Media assets
│   ├── logo.png              # Company logo
│   ├── favicon.ico           # Website icon
│   ├── services/             # Service images
│   ├── articles/             # Article images
│   ├── videos/               # Video thumbnails
│   └── ...                   # Other images
├── 📄 greenlife_wellness.sql  # Database schema
└── 📄 README.md              # This file
```

---

## 👥 User Roles

### 🔐 **Admin**
- **Full System Access**: Complete control over all features
- **User Management**: Create, edit, delete users
- **Service Management**: Add, modify, remove services
- **Resource Management**: Manage articles, videos, tips
- **Analytics**: View system statistics and reports

### 👨‍⚕️ **Therapist**
- **Client Management**: View assigned clients
- **Appointment Management**: Schedule and manage sessions
- **Progress Tracking**: Monitor client progress
- **Inquiry Response**: Respond to client inquiries
- **Session Notes**: Document therapy sessions

### 👤 **Client**
- **Profile Management**: Update personal information
- **Appointment Booking**: Schedule therapy sessions
- **Progress Monitoring**: Track personal progress
- **Resource Access**: View articles and videos
- **Inquiry Submission**: Contact therapists

---

## 🛠️ Technologies Used

### **Frontend**
- **HTML5**: Semantic markup and structure
- **CSS3**: Modern styling with flexbox and grid
- **JavaScript**: Interactive functionality
- **Font Awesome**: Icon library
- **Responsive Design**: Mobile-first approach

### **Backend**
- **PHP 7.4+**: Server-side scripting
- **MySQL 8.0+**: Database management
- **Session Management**: Secure user authentication
- **File Upload**: Image and document handling
- **Security**: Input validation and SQL injection prevention

### **Database**
- **MySQL**: Relational database
- **15+ Tables**: Comprehensive data structure
- **Foreign Keys**: Data integrity
- **Indexes**: Performance optimization
- **Sample Data**: Ready-to-use test data

---

## 📊 Database Schema

### **Core Tables**
- `users` - User accounts (clients, therapists, admins)
- `services` - Wellness services offered
- `appointments` - Booking and scheduling
- `sessions` - Therapy session records
- `client_progress` - Progress tracking

### **Resource Tables**
- `articles` - Wellness articles and blogs
- `videos` - Educational videos and tutorials
- `health_tips` - Daily health tips
- `inquiries` - Client inquiries and responses

### **Management Tables**
- `therapists` - Detailed therapist information
- `therapist_services` - Service-therapist relationships
- `therapist_availability` - Scheduling availability
- `notifications` - System notifications

---

## 🎨 Design Features

### **Visual Design**
- **Color Palette**: Natural greens and calming blues
- **Typography**: Clean, readable fonts
- **Layout**: Card-based design with clear hierarchy
- **Images**: High-quality wellness imagery
- **Icons**: Consistent Font Awesome iconography

### **User Experience**
- **Navigation**: Intuitive menu structure
- **Search**: Service and resource search functionality
- **Filtering**: Category-based content filtering
- **Responsive**: Seamless mobile experience
- **Accessibility**: ARIA labels and keyboard navigation

### **Interactive Elements**
- **Hover Effects**: Smooth transitions
- **Modal Dialogs**: Clean popup interfaces
- **Form Validation**: Real-time input validation
- **Progress Indicators**: Visual progress tracking
- **Status Badges**: Clear status communication

---

## 📱 Screenshots

### 🏠 Homepage
![Homepage](images/hero-image.jpg)
*Welcome to GreenLife - Your holistic wellness journey begins here*

### 👨‍💼 Admin Dashboard
![Admin Dashboard](images/admin.jpg)
*Comprehensive management interface for system administrators*

### 👨‍⚕️ Therapist Dashboard
![Therapist Dashboard](images/therapist.jpg)
*Professional interface for wellness therapists*

### 👤 Client Dashboard
![Client Dashboard](images/client.jpg)
*Personal wellness tracking and appointment management*

---

## 🔧 Configuration

### **Database Configuration**
```php
// php/dbconnect.php
$host = "localhost";
$user = "your_username";
$pass = "your_password";
$dbname = "greenlife_wellness";
```

### **File Upload Settings**
```php
// Configure in php.ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

### **Session Configuration**
```php
// Security settings
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

---

## 📝 API Endpoints

### **Authentication**
- `POST /php/login.php` - User login
- `GET /php/logout.php` - User logout
- `POST /php/register.php` - User registration

### **Dashboard Access**
- `GET /php/admin_dashboard.php` - Admin panel
- `GET /php/therapist_dashboard.php` - Therapist panel
- `GET /php/client_dashboard.php` - Client panel

### **CRUD Operations**
- `POST /php/add_client.php` - Add new client
- `POST /php/add_therapist.php` - Add new therapist
- `POST /php/add_service.php` - Add new service
- `POST /php/add_article.php` - Add new article
- `POST /php/add_video.php` - Add new video
- `POST /php/add_tip.php` - Add new health tip

### **Data Retrieval**
- `GET /php/get_services.php` - Fetch services
- `GET /php/get_resources.php` - Fetch resources
- `GET /php/therapists.php` - Fetch therapists
- `GET /php/resources.php` - Fetch resource library

---

## 🤝 Contributing

We welcome contributions to improve GreenLife Wellness Center! Here's how you can help:

### **Ways to Contribute**
1. **Bug Reports**: Report issues and bugs
2. **Feature Requests**: Suggest new features
3. **Code Contributions**: Submit pull requests
4. **Documentation**: Improve documentation
5. **Testing**: Test new features and report issues

### **Development Guidelines**
1. **Code Style**: Follow PSR-12 PHP standards
2. **Database**: Use prepared statements for security
3. **Frontend**: Maintain responsive design
4. **Testing**: Test on multiple browsers and devices
5. **Documentation**: Update README for new features

### **Pull Request Process**
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request
6. Respond to feedback

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2025 GreenLife Wellness Center

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 📞 Contact

### **GreenLife Wellness Center**
- **Location**: 123 Green Road, Colombo, Sri Lanka
- **Phone**: +94 11 234 5678
- **Email**: wellness@greenlife.lk
- **Website**: [www.greenlife.lk](https://www.greenlife.lk)

### **Social Media**
- **Facebook**: [GreenLife Wellness Center](https://www.facebook.com/GreenLifeWellnessCenter)
- **Instagram**: [@GreenLifeWellnessCenter](https://www.instagram.com/GreenLifeWellnessCenter)
- **Twitter**: [@GreenLifeWellness](https://twitter.com/GreenLifeWellness)

### **Development Team**
- **Lead Developer**: [Your Name](mailto:developer@greenlife.lk)
- **UI/UX Designer**: [Designer Name](mailto:designer@greenlife.lk)
- **Project Manager**: [PM Name](mailto:pm@greenlife.lk)

---

<div align="center">

**🌟 Thank you for choosing GreenLife Wellness Center 🌟**

*Embrace your well-being with us*

![Footer Logo](images/logo.png)

</div>
