# User Module - Blood Donation Portal

## Overview
This user module provides authentication and user management features for the Blood Donation Portal. It includes user registration (signup), login, logout, and a comprehensive user dashboard.

## Features

### 1. **User Signup** (`signup.php`)
- Create a new user account with username, email, phone, blood group, and optional profile picture
- Password validation and confirmation
- Check for duplicate usernames/emails
- Secure password hashing using bcrypt
- Automatic redirect to login after successful signup

### 2. **User Login** (`login.php`)
- Authenticate users with username and password
- Session management
- Password verification using bcrypt
- Error messages for invalid credentials

### 3. **User Dashboard** (`dashboard.php`)
- View user profile information including donation stats (these appear only once an admin records a donation), health condition, and last donation hospital
- Display donation history from donation_records (with date, units, hospital, donor name)
- Show the latest blood requests
- Quick links to important pages (edit profile, download certificate, find donors, logout)
- Certificate download page with hospital info
- User-friendly interface with sidebar navigation

### 4. **Edit Profile** (`edit_profile.php`)
- Update phone number, blood group and health condition (willing to donate + reason)
- Enter last donation date, total donations and units donated
- Change password functionality
- Profile validation

### 5. **User Logout** (`logout.php`)
- Destroy session and redirect to login page

### 6. **Database and Admin Features**
- New **donation_records** table to track each donation event
- Admin section with user management (`admin/manage_users.php`) where admins can edit/delete user accounts
- Donation management integrated into donor management and a dedicated record list:
  - Edit donor details (`admin/edit_donor.php`)
  - Record a donation event (`admin/record_donation.php`) with units, hospital and date
  - View all recorded donations (`admin/manage_donations.php`), edit (`admin/edit_donation.php`) or delete them
  - Updated stats in user profiles automatically
  - Admin can delete donation registrations as needed

### 4. **Edit Profile** (`edit_profile.php`)
- Update phone number, blood group and health condition (willing to donate + reason)
- Enter last donation date, total donations and units donated
- Change password functionality
- Profile validation

### 5. **User Logout** (`logout.php`)
- Destroy session and redirect to login page

## Database Setup

The user table now includes an optional `profile_pic` column to store a path to an uploaded image. If migrating from an existing installation you should also add this column.


### Required Users Table
Run the SQL script to create the users table:

```sql
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    blood_group VARCHAR(5),
    willing_to_donate TINYINT(1) DEFAULT 1,
    donate_reason TEXT,
    last_donation_date DATE DEFAULT NULL,
    donation_count INT DEFAULT 0,
    donation_units INT DEFAULT 0,
    profile_pic VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_email ON users(email);
```

If you already have an existing `users` table, run the following ALTER statements instead to add the new columns:

```sql
ALTER TABLE users
  ADD COLUMN willing_to_donate TINYINT(1) DEFAULT 1,
  ADD COLUMN donate_reason TEXT NULL,
  ADD COLUMN last_donation_date DATE NULL,
  ADD COLUMN donation_count INT DEFAULT 0,
  ADD COLUMN donation_units INT DEFAULT 0,
  ADD COLUMN profile_pic VARCHAR(255) DEFAULT NULL;
```

Or use the updated `database_schema.sql` file which contains the full definition.

Or use the provided `database_schema.sql` file:
1. Open phpMyAdmin
2. Select your `blood_portal` database
3. Click "SQL" tab
4. Copy and paste the contents of `database_schema.sql`
5. Click "Go" to execute

## File Structure

```
users/
├── signup.php              # User registration page
├── login.php               # User login page
├── dashboard.php           # User dashboard
├── edit_profile.php        # Edit user profile
├── logout.php              # User logout handler
├── database_schema.sql     # Database table schema
└── README.md              # This file
```

## Usage

### For New Users
1. Navigate to "User Signup" link from the homepage
2. Fill in registration details (username, email, phone, blood group)
3. Create a strong password
4. Click "Sign Up" to create account
5. You'll be redirected to login page

### For Existing Users
1. Click "User Login" from the navigation
2. Enter username and password
3. Click "Login" to access dashboard
4. View profile, donation history, and blood requests
5. Click "Edit Profile" to update information
6. Click "Logout" to end session

## Session Variables

After login, the following session variables are set:
- `$_SESSION['user_id']` - Unique user ID
- `$_SESSION['username']` - Username
- `$_SESSION['email']` - User Email

## Security Features

1. **Password Hashing**: Uses bcrypt for secure password storage
2. **SQL Injection Prevention**: Uses prepared statements with bind parameters
3. **Session Management**: Checks session before accessing protected pages
4. **Input Validation**: Validates all user inputs
5. **XSS Protection**: Uses htmlspecialchars() for output escaping

## Validation Rules

### Signup
- Username: Minimum 3 characters
- Email: Valid email format
- Password: Minimum 6 characters
- Confirm Password: Must match password
- Phone: Required field

### Login
- Username: Required
- Password: Required

### Profile Update
- Phone: Required
- New Password (optional): Minimum 6 characters if provided
- Confirm Password: Must match new password if provided

## Error Handling

The module includes error handling for:
- Empty form fields
- Invalid email format
- Password mismatch
- Duplicate username/email
- Database connection errors
- SQL execution errors

## Integration with Existing System

The user module integrates with:
- **Donors Table**: Displays donation history if user registered as donor
- **Requests Table**: Shows blood requests made by user
- **Config.php**: Uses database connection settings

## Future Enhancements

Possible additions to this module:
- Email verification for new signups
- Password reset functionality
- Two-factor authentication
- User profile picture upload
- Activity history/logs
- Notification system
- Role-based access control

## Troubleshooting

### Users table not created
- Ensure you've run the SQL script in database_schema.sql
- Check database permissions

### Login not working
- Verify username and password are correct
- Check if users table exists and has data
- Check database connection in config.php

### Session expires
- Users need to login again if session expires
- Session timeout is determined by PHP configuration

## Support

For issues or questions about the user module, review the code comments or check the error messages displayed in the browser.
