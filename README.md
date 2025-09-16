# Student Attendance Management System

A comprehensive web-based attendance management system built with Laravel and Vue.js, designed to streamline attendance tracking for educational institutions.

## 📋 Description

The Student Attendance Management System is a modern, full-featured application that enables educational institutions to efficiently manage student attendance across multiple classes, courses, and sessions. The system provides role-based access for administrators, lecturers, and students, with automated notifications and comprehensive reporting capabilities.

## 🛠️ Tech Stack

### Backend
- **Laravel 9.x** - PHP framework
- **PHP 8.0+** - Server-side programming language
- **MySQL** - Database management system

### Frontend
- **Inertia.js** - Modern monolith approach
- **Tailwind CSS** - Utility-first CSS framework

### Additional Libraries & Tools
- **Chart.js** - Data visualization
- **DataTables** - Advanced table interactions
- **Laravel Excel** - Excel file import/export
- **Laravel DomPDF** - PDF generation
- **PHPMailer** - Email functionality

## ✨ Key Features

### 🔐 Multi-Role Authentication System
- **Admin Panel** - Complete system management
- **Lecturer Dashboard** - Session and attendance management
- **Student Portal** - Personal attendance tracking
- Secure password reset functionality for all user types

### 📊 Attendance Management
- Real-time attendance tracking during sessions
- Automated session status management (Scheduled → Running → Ended)
- Comprehensive attendance reports and analytics
- Bulk attendance operations

### 📚 Academic Management
- **Course Management** - Create and manage courses
- **Class Organization** - Department, Level, and Section-based class structure
- **Session Scheduling** - Time-based session management with venues
- **Student Enrollment** - Organized by classes and academic levels

### 📧 Automated Notifications
- **Email Notifications** for session events:
  - Session scheduled notifications
  - Session started alerts
  - Session ended confirmations
- Automated email delivery to lecturers and students
- Queue-based email processing for better performance

### 📈 Data Import/Export
- **Bulk Student Import** - Excel-based student data import with validation
- **Bulk Lecturer Import** - Staff data import functionality
- **Data Export** - Generate reports in various formats
- **PDF Generation** - Attendance reports and summaries

### 🎯 Advanced Features
- **Real-time Dashboard** - Live statistics and charts
- **DataTables Integration** - Advanced table filtering and sorting
- **Responsive Design** - Mobile-friendly interface
- **Soft Deletes** - Data recovery capabilities
- **Comprehensive Validation** - Data integrity and security

### 🔧 System Administration
- **User Management** - Create, update, and manage user accounts
- **System Monitoring** - Track system usage and performance
- **Database Management** - Automated migrations and seeders
- **Queue Management** - Background job processing

## 🚀 Installation

### Prerequisites
- PHP 8.0 or higher
- Composer
- Node.js and npm
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/attendance-app.git
   cd attendance-app
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   # Configure your database settings in .env file
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

7. **Start the application**
   ```bash
   php artisan serve
   ```

## 📱 Screenshots

### Dashboard Overview
![Dashboard](public/img/dashboard-screenshot.png)
*Comprehensive dashboard showing attendance statistics, recent sessions, and system overview*

### Attendance Management
![Attendance](public/img/attendance-screenshot.png)
*Real-time attendance tracking interface with student list and session details*

### Session Management
![Sessions](public/img/sessions-screenshot.png)
*Session scheduling and management interface for lecturers*

## 🏗️ System Architecture

### Database Structure
- **Users** - Multi-role user management (Admin, Lecturer, Student)
- **Sessions** - Class sessions with scheduling and status tracking
- **Attendance** - Student attendance records linked to sessions
- **Academic Structure** - Departments, Levels, Sections, and Classes
- **Courses** - Course management and lecturer assignments

### Key Models
- `Student` - Student information and attendance tracking
- `Lecturer` - Lecturer profiles and session management
- `Admin` - Administrative user management
- `Session` - Class sessions with automated status management
- `Attendance` - Attendance records with soft delete support

## 🔄 Automated Features

### Session Management
The system includes automated session management through console commands:
- **Session Status Updates** - Automatically changes session status based on time
- **Email Notifications** - Sends notifications when sessions start/end
- **Background Processing** - Queue-based email delivery

### Data Processing
- **Bulk Import Validation** - Comprehensive data validation during import
- **Error Handling** - Graceful error handling with detailed feedback
- **Data Integrity** - Ensures data consistency across the system

## 📋 Usage

### For Administrators
1. Access the admin dashboard
2. Manage users, courses, and system settings
3. Import student and lecturer data
4. Generate comprehensive reports
5. Monitor system performance

### For Lecturers
1. Log in to the lecturer portal
2. Create and manage class sessions
3. Track student attendance in real-time
4. View attendance reports and analytics
5. Receive automated session notifications

### For Students
1. Access the student portal
2. View personal attendance records
3. Check upcoming sessions
4. Receive session notifications
5. Track attendance statistics

## 🔧 Configuration

### Email Configuration
Configure your email settings in the `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

### Queue Configuration
For email notifications, configure your queue driver:
```env
QUEUE_CONNECTION=database
```

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

We welcome contributions! Please feel free to submit a Pull Request.

## 📞 Contact

For support or questions, please contact the development team or create an issue in the repository.

---

**Built with ❤️ using Laravel and InertiaJs**