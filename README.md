# User Activity Tracking and Inactivity Monitoring System

A comprehensive Laravel-based application that provides real-time user activity monitoring, inactivity detection, and automated response management. The system includes a full admin panel, secure authentication, and automated deployment capabilities.

## 🚀 Features

### Core Functionality
- **User Authentication**: Role-based access control (User/Admin)
- **Activity Logging**: Comprehensive tracking of all user actions
- **Inactivity Monitoring**: Browser-based detection with escalating alerts
- **Penalty System**: Automated penalty management for repeated inactivity
- **File Management**: Secure upload/download functionality
- **Admin Dashboard**: Comprehensive analytics and management interface

### Technical Features
- **Frontend**: Blade templates with Tailwind CSS and vanilla JavaScript
- **Backend**: Laravel 12 with Eloquent ORM
- **Database**: MySQL with structured data models
- **Deployment**: GitHub Actions with secure secrets management

## 📋 Table of Contents

1. [Installation](#installation)
2. [Database Schema](#database-schema)
3. [Authentication System](#authentication-system)
4. [Activity Logging](#activity-logging)
5. [Inactivity Monitoring](#inactivity-monitoring)
6. [Penalty System](#penalty-system)
7. [Admin Dashboard](#admin-dashboard)
8. [Settings Management](#settings-management)
9. [API Endpoints](#api-endpoints)
10. [Deployment](#deployment)
11. [Security](#security)

## 🛠️ Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### Setup Instructions

1. **Clone the repository**
```bash
git clone <your-repo-url>
cd user-monitoring
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install Node.js dependencies**
```bash
npm install
```

4. **Set up environment variables**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure database connection** in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. **Run migrations and seeders**
```bash
php artisan migrate --seed
```

7. **Build assets**
```bash
npm run build
```

8. **Start the development server**
```bash
php artisan serve
```

## 🔐 Authentication System

The application implements role-based authentication with two user types:

### User Roles
- **Admin**: Full access to admin panel, user management, activity logs, and settings
- **User**: Basic access with activity monitoring

### Default Users
The seeder creates two default users:
- **Admin**: `admin@example.com` / `password`
- **User**: `user@example.com` / `password`

### Authentication Middleware
- `auth`: Standard authentication
- `admin`: Role-based admin access
- `activity-log`: Automatic activity logging middleware

## 🗃️ Database Schema

The system uses six main database tables:

### Users Table
- Extends Laravel's default users table
- Additional `role` field (enum: 'user', 'admin')

### Activity Logs Table
- `user_id`: Foreign key to users
- `action`: Type of action ('create', 'read', 'update', 'delete', 'login', 'logout', etc.)
- `model_type`: Model affected (optional)
- `model_id`: ID of affected record (optional)
- `ip_address`: User's IP
- `user_agent`: Browser information
- `metadata`: Additional action data (JSON)

### Idle Sessions Table
- `user_id`: Foreign key to users
- `start_time`: When inactivity started
- `end_time`: When session ended (optional)
- `duration`: Length of idle session in seconds
- `session_id`: Unique session identifier

### Penalties Table
- `user_id`: Foreign key to users
- `reason`: Why penalty was issued
- `penalty_count`: Number of penalties (escalating)
- `active`: Whether penalty is currently active
- `expires_at`: When penalty expires (optional)

### Settings Table
- `key`: Setting identifier (e.g., 'idle_timeout', 'monitoring_enabled')
- `value`: Setting value
- `description`: Human-readable description

### User Files Table
- `user_id`: Foreign key to users
- `original_name`: Original filename
- `stored_name`: Securely stored filename
- `file_path`: Path to file in storage
- `file_size`: File size in bytes
- `mime_type`: MIME type of file

## 📊 Activity Logging

### Automatic Logging
The system automatically logs various user activities:
- Login/logout events
- CRUD operations on models
- Profile updates
- File uploads/downloads
- Inactivity events

### Manual Logging
Use the `ActivityLogService` for custom logging:
```php
use App\Services\ActivityLogService;

ActivityLogService::log('custom_action', 'ModelType', $modelId, $metadata);
```

### Middleware Integration
The `ActivityLogMiddleware` automatically logs route-based activities while excluding sensitive routes.

## ⏰ Inactivity Monitoring

### JavaScript Implementation
- Tracks mouse movement, keyboard input, scrolling, clicks
- Page visibility detection
- Configurable timeout via settings
- Three-tier alert system:
  1. First warning at 10% of timeout
  2. Second warning at 5% of timeout  
  3. Auto logout after timeout

### Backend Integration
- API endpoints for activity pings
- Penalty application on timeout
- Session tracking

### Configurable Settings
- Default timeout: 300 seconds (5 minutes)
- Adjustable via admin panel
- Dynamic fetching from database

## ⚖️ Penalty System

### Escalating Penalties
- **Level 1**: First inactivity - warning
- **Level 2**: Second inactivity - stronger warning
- **Level 3**: Third inactivity - automatic logout + penalty

### Penalty Management
- Admin can deactivate penalties
- Clear all penalties for a user
- Track penalty history
- Configurable expiration

### Penalty Service
The `PenaltyService` handles:
- Penalty creation and tracking
- Active penalty management
- Escalation logic
- Cleanup of expired penalties

## 🎛️ Admin Dashboard

### Statistics Overview
- Total users count
- Active/inactive users
- Today's activities
- Active penalties
- Idle sessions

### Management Features
- User CRUD operations
- Activity log filtering and search
- Penalty management
- Settings configuration
- File management

### Data Visualization
- Statistics cards with visual indicators
- Recent activity tables
- Charts (implemented via backend data)

## ⚙️ Settings Management

### Configurable Parameters
- `idle_timeout`: Inactivity timeout in seconds
- `monitoring_enabled`: Toggle monitoring on/off
- Additional custom settings possible

### Dynamic Configuration
- Settings loaded from database
- Real-time updates without restart
- API endpoints for client-side access

## 🌐 API Endpoints

### Inactivity Endpoints
- `POST /api/inactivity/penalty` - Apply penalty for timeout
- `POST /api/activity/ping` - Activity heartbeat

### Settings Endpoints
- `GET /admin/settings/timeout` - Get idle timeout
- `GET /admin/settings/monitoring` - Get monitoring status

### Authentication Endpoints
- Standard Laravel Breeze endpoints
- Login/logout functionality
- Password reset

## 🚀 Deployment

### GitHub Actions
The system includes a complete deployment workflow in `.github/workflows/deploy.yml`:

### Deployment Features
- **Conditional Steps**: Using `static` keyword `false` as required
- **Secure Secrets**: SERVER_HOST, SERVER_USERNAME, SERVER_SSH_KEY, DEPLOY_PATH, BRANCH_NAME
- **Full Laravel Process**: Dependencies, asset building, migrations, cache clearing
- **Rollback Support**: Git reset to specific branch

### Deployment Steps
1. Checkout code
2. Setup PHP/Node.js environments  
3. Install dependencies
4. Build frontend assets
5. Deploy via SSH with conditional steps
6. Run Laravel commands (migrate, clear caches)

## 🔒 Security Features

### Authentication Security
- Laravel's built-in authentication
- Role-based access control
- Session management
- CSRF protection

### Data Security
- File upload validation
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade templating)
- Input sanitization

### Activity Security
- IP address logging
- User agent tracking
- Secure file storage
- Permission-based access

## 🏗️ Architecture

### Frontend
- **Framework**: Vanilla JavaScript/jQuery (as required)
- **Templates**: Blade with Tailwind CSS
- **Components**: Alpine.js for dynamic features
- **Styling**: Tailwind CSS with custom enhancements

### Backend
- **Framework**: Laravel 12
- **Architecture**: MVC pattern
- **Services**: ActivityLogService, PenaltyService
- **Middleware**: Custom authentication and logging middleware

### Database
- **ORM**: Eloquent
- **Migrations**: Structured schema management
- **Seeding**: Default data setup
- **Relationships**: Proper foreign key constraints

## 🧪 Testing

The application includes:
- Database migrations with proper constraints
- Seeder files for development data
- Comprehensive error handling
- Input validation on all forms

## 📚 Usage Guide

### For End Users
1. Register/login to the application
2. Navigate to dashboard
3. System automatically monitors activity
4. Receive warnings upon inactivity
5. Maintain activity to stay logged in

### For Administrators
1. Log in with admin credentials
2. Access admin dashboard at `/admin`
3. Manage users, view logs, manage penalties
4. Configure system settings
5. Monitor system activity

### For Developers
1. Follow installation instructions
2. Customize models, views, and controllers as needed
3. Extend functionality using provided services
4. Add new settings via settings table
5. Create additional activity types as needed

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a pull request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

This User Activity Tracking and Inactivity Monitoring System was developed for educational and demonstration purposes. It showcases advanced Laravel features, security practices, and modern web development techniques.