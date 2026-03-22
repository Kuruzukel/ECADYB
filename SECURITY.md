# Security Policy

## Supported Versions

We actively maintain and provide security updates for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |

## Reporting a Vulnerability

We take the security of the Graduation Gallery Application seriously. If you discover a security vulnerability, please follow these steps:

### How to Report

1. **Do NOT** open a public issue
2. Email the security team with details about the vulnerability
3. Include the following information:
   - Description of the vulnerability
   - Steps to reproduce the issue
   - Potential impact
   - Suggested fix (if available)

### What to Expect

- **Initial Response**: Within 48 hours of your report
- **Status Updates**: Regular updates on the progress of addressing the vulnerability
- **Resolution Timeline**: We aim to resolve critical vulnerabilities within 7 days
- **Credit**: Security researchers will be credited (unless they prefer to remain anonymous)

## Security Best Practices

### Authentication & Authorization

- **Password Security**: All passwords are hashed using secure algorithms before storage
- **Session Management**: JWT-based authentication with secure token handling
- **Role-Based Access Control (RBAC)**: Multi-tier permission system for admin and student roles
- **Session Timeout**: Automatic session expiration after inactivity

### Data Protection

- **Environment Variables**: Sensitive credentials stored in `.env` files (never committed to version control)
- **Database Security**: MongoDB connection strings use authentication and encryption
- **Input Validation**: All user inputs are validated and sanitized
- **SQL/NoSQL Injection Prevention**: Parameterized queries and input sanitization

### Infrastructure Security

- **HTTPS Only**: All production deployments must use HTTPS
- **CDN Security**: Bunny.net CDN with access key authentication
- **API Key Protection**: SendGrid and other API keys stored securely in environment variables
- **CORS Configuration**: Proper Cross-Origin Resource Sharing policies

### File Upload Security

- **File Type Validation**: Only allowed file types can be uploaded
- **File Size Limits**: Maximum file size restrictions enforced
- **Malware Scanning**: Recommended for production environments
- **Secure Storage**: Files stored on CDN with proper access controls

## Security Features

### Implemented Security Measures

1. **Authentication System**
   - Secure login with password hashing
   - JWT token-based session management
   - Password reset functionality with email verification

2. **Authorization Controls**
   - Role-based access control (Admin/Student)
   - Permission-based feature access
   - Secure API endpoints

3. **Data Encryption**
   - Passwords hashed before storage
   - Secure transmission over HTTPS
   - Environment variable protection

4. **Input Sanitization**
   - XSS prevention
   - CSRF protection
   - SQL/NoSQL injection prevention

5. **Secure Configuration**
   - `.env` files for sensitive data
   - `.gitignore` configured to exclude credentials
   - Secure headers configuration

## Compliance

This application follows security best practices including:

- OWASP Top 10 security guidelines
- Secure coding standards
- Data protection principles
- Privacy by design

## Security Updates

Security patches and updates are released as needed. Users are encouraged to:

- Keep dependencies up to date
- Monitor security advisories
- Apply patches promptly
- Review security logs regularly

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [MongoDB Security Checklist](https://docs.mongodb.com/manual/administration/security-checklist/)

## Contact

For security-related inquiries, please contact the development team through the appropriate channels.

---

**Last Updated**: March 2026

_Security is a continuous process. We regularly review and update our security measures to protect our users and their data._
