# Secure Authentication System

A robust PHP-based authentication system with two-factor authentication (2FA) and role-based access control.

## Features

- User registration with email validation
- Two-factor authentication using Google Authenticator
- Role-based access control (Admin/User)
- Secure session management
- Content management system
- Responsive Bootstrap UI
- MySQL database integration

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Google Authenticator app (for 2FA)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/your-username/secure-auth-system.git
```

2. Configure the database:
- Create a MySQL database
- Import the database schema from `database/schema.sql`
- Update database credentials in `config/database.php`

3. Configure the application:
- Set up your web server to point to the project directory
- Ensure proper file permissions
- Configure PHP settings for session handling

4. Access the application:
- Open your web browser
- Navigate to the application URL
- Register a new account
- Set up 2FA using Google Authenticator

## Directory Structure

```
secure-auth-system/
├── classes/           # PHP classes
├── config/           # Configuration files
├── database/         # Database schema and migrations
├── includes/         # Common includes
├── assets/          # Static assets (CSS, JS, images)
└── public/          # Publicly accessible files
```

## Security Features

- Password hashing using PHP's password_hash()
- Prepared statements for SQL queries
- CSRF protection
- Session management with expiration
- Input validation and sanitization
- Secure 2FA implementation
- Role-based access control

## Usage

1. Registration:
   - Fill out the registration form
   - Verify your email
   - Set up 2FA

2. Login:
   - Enter your credentials
   - Provide 2FA code
   - Access your dashboard

3. Dashboard:
   - Admins can manage content
   - Users can view content
   - Manage your profile

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue in the GitHub repository.

## Acknowledgments

- Bootstrap for the UI framework
- Google Authenticator for 2FA
- PHP community for best practices 