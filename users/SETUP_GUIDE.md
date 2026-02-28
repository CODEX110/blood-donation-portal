# User Module - Setup Guide

## Quick Start Guide

### Step 1: Create the Users Table

1. Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`)
2. Select your **blood_portal** database
3. Click on the **SQL** tab
4. Copy and paste the following SQL code:

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

> **Note:** The users table has been extended with extra columns for donation tracking and optional profile picture:
> - `willing_to_donate` (boolean) indicates if the user is currently willing to donate
> - `donate_reason` (text) stores the optional reason when not willing
> - `last_donation_date` records the most recent donation date
> - `donation_count` and `donation_units` track the number of donations and total units given (these values are updated only by an admin when a donation record is created)
> - `profile_pic` stores a relative path to the user's uploaded image
> 
These fields power the dashboard health status, update forms, certificate, and profile picture display.

5. Click **GO** to execute the query
6. You should see a success message

### Step 2: Verify Files Are Created

Check that all files exist in `c:\xampp\htdocs\blood-donation\users\`:
- ✓ `signup.php` - User registration
- ✓ `login.php` - User login
- ✓ `dashboard.php` - User dashboard
- ✓ `edit_profile.php` - Edit profile
- ✓ `logout.php` - Logout handler
- ✓ `database_schema.sql` - Database schema file
- ✓ `README.md` - Documentation

### Step 3: Test the Module

1. **Start XAMPP** - Make sure Apache and MySQL are running

2. **Access the Portal**:
   - Go to `http://localhost/blood-donation/index.php`
   - You should see links for "User Login" and "User Signup"   - Admin users can open the admin dashboard via `http://localhost/blood-donation/admin/login.php` to manage donors, users, requests, and record donation events.
3. **Test Signup**:
   - Click "User Signup"
   - Fill in the form (you may optionally upload a profile picture):
     - Username: `testuser`
     - Email: `testuser@example.com`
     - Phone: `9876543210`
     - Blood Group: `O+`
     - Password: `password123`
     - Confirm Password: `password123`
   - Click "Sign Up"
   - You should get a success message and redirect to login

4. **Test Login**:
   - Click "User Login"
   - Enter credentials:
     - Username: `testuser`
     - Password: `password123`
   - Click "Login"
   - You should see the user dashboard

5. **Explore Dashboard**:
   - View your profile information
   - Check donation history (will be empty if not registered as donor)
   - Check blood requests (will be empty if none made)
   - Click "Edit Profile" to update information
   - Click "Logout" to test logout

> **Admin note:** If you have admin access, visit `admin/manage_users.php` or `admin/manage_donors.php` after logging in to edit/delete users and donors. Use the "Donated" action on a donor to record a donation event (units, hospital, date) which will automatically update the associated user profile and be reflected on the certificate.

### Step 4: Register as Donor (Optional)

To see donation history in dashboard:
1. Go back to `http://localhost/blood-donation/register.php`
2. Register as a donor with the same email as your user account
3. Go to user dashboard to see the donation record

## Navigation Updates

The main navigation bar has been updated to include:
- **User Login** - `users/login.php`
- **User Signup** - `users/signup.php`

These are now visible in the main navigation alongside Admin login.

Admin pages available after logging in at `admin/login.php`:
- Manage Donors (`admin/manage_donors.php`)
- Manage Users (`admin/manage_users.php`)
- Manage Donations (`admin/manage_donations.php`)
- Manage Emergency Requests (`admin/manage_requests.php`)

## Module Features

### Signup Process
```
User visits signup.php
    ↓
Fills form with username, email, phone, blood group, password
    ↓
System validates input (min lengths, format, no duplicates)
    ↓
Password hashed using bcrypt
    ↓
User record created in database
    ↓
Redirect to login page
```

### Login Process
```
User visits login.php
    ↓
Enters username and password
    ↓
System checks database for username
    ↓
Password verified using bcrypt
    ↓
Session created with user_id, username, email
    ↓
Redirect to dashboard
```

### Dashboard Features
- Display user profile (username, email, phone, blood group, member since)
- Show donation history from donors table (if user registered as donor)
- Show blood requests from requests table (if user created requests)
- Quick navigation sidebar
- Edit profile option
- Logout functionality

## Security Features Implemented

1. **Password Security**
   - Bcrypt hashing (industry standard)
   - Password validation rules
   - Secure password comparison

2. **Database Security**
   - Prepared statements to prevent SQL injection
   - Parameter binding
   - Input sanitization

3. **Session Security**
   - Session protection requiring login
   - Session variables for authentication
   - Logout destroys session

4. **Input Validation**
   - Email format validation
   - Username length requirements
   - Password confirmation
   - Field requirement checks

5. **Output Security**
   - htmlspecialchars() for XSS prevention
   - Safe data display in HTML

## Troubleshooting

### Table Creation Failed
- ✓ Check MySQL is running
- ✓ Verify blood_portal database exists
- ✓ Check database privileges
- ✓ Try running query again

### Signup Fails with "User already exists"
- ✓ Clear database and start fresh, OR
- ✓ Use different username/email

### Login Returns "Invalid credentials"
- ✓ Check username is exactly as registered (case-sensitive)
- ✓ Verify correct password
- ✓ Check users table has the registration record

### Session Error/Not Logged In
- ✓ Navigate directly to login.php
- ✓ Login again
- ✓ Check browser cookies are enabled

### Page Shows Blank/Error
- ✓ Check if config.php is correct
- ✓ Verify database connection working
- ✓ Check error logs in browser console

## Database Schema Verification

To verify the users table was created correctly:

1. In phpMyAdmin, select blood_portal database
2. You should see "users" table in the left panel
3. Click on users table structure to verify columns

Expected columns:
- `id` - INT, Auto Increment, Primary Key
- `username` - VARCHAR(50), Unique, Not Null
- `email` - VARCHAR(100), Unique, Not Null
- `password` - VARCHAR(255), Not Null
- `phone` - VARCHAR(20), Not Null
- `blood_group` - VARCHAR(5)
- `created_at` - TIMESTAMP, Default Current
- `updated_at` - TIMESTAMP, Default Current

## What's Next?

After successful setup, you can:
1. **Add More Users** - Repeat signup/login testing
2. **Integrate with Donors** - Register users as donors to see history
3. **Create Requests** - Make blood requests to see in dashboard
4. **Customize Styling** - Modify CSS in ../css/style.css
5. **Add Features** - Consider password reset, profile pic, etc.

## Files Modified

- `index.php` - Added user login/signup navigation links
- `users/` directory - Created with all new module files

## Questions?

Refer to detailed documentation in `users/README.md`
