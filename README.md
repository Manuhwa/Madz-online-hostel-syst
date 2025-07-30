# Hostel Management System

A comprehensive web-based hostel management system built with PHP and MySQL. This system helps manage hostel operations, student accommodations, and maintenance requests efficiently.

## Features

### Admin Features
- Dashboard with system overview
- Hostel management (add, edit, delete hostels)
- Student management
- Room allocation
- Maintenance request handling
- News and updates management
- CSV data import functionality

### Student Features
- View room allocation
- Submit maintenance requests
- View news and updates
- Profile management

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- XAMPP/WAMP/MAMP (for local development)

## Installation

1. Clone or download this repository to your web server's root directory
2. Create a MySQL database named `hostel_management`
3. Import the database schema from `config/schema.sql`
4. Configure database connection in `config/database.php`
5. Access the system through your web browser

## Default Admin Account

After installation, you can create an admin account using the following SQL:

```sql
INSERT INTO users (username, password, role) 
VALUES ('admin', '$2y$10$YourHashedPasswordHere', 'admin');
```

Note: Replace `$2y$10$YourHashedPasswordHere` with a proper password hash generated using PHP's `password_hash()` function.

## Directory Structure

```
├── admin/              # Admin panel files
├── student/            # Student panel files
├── config/            # Configuration files
├── assets/            # CSS, JS, and images
├── index.php          # Login page
├── logout.php         # Logout script
└── README.md          # This file
```

## Security Features

- Password hashing
- Session management
- SQL injection prevention
- XSS protection
- CSRF protection

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 