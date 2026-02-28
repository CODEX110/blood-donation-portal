# Blood Donation Portal

This is a simple blood donation portal designed for Universal College of Arts and Science, Mannarkkad. It allows students and staff to register as donors and helps those in need find matching donors. An admin module is included for managing donor records.

## Technologies
- HTML, CSS, JavaScript
- PHP (backend)
- MySQL (database)

## Features
- Donor registration form with validation
- Search donors by blood group
- Admin login and dashboard
- Manage donors (view/delete)
- Responsive, clean UI with custom styles
- Slideshow on home page with full‑view images, arrows and indicators
- Hero banner with typewriter headline and animated intro text
- Chatbot widget on home page for basic queries
- Emergency blood request form and admin view
- Donor registration now collects role (Student/Staff) and department
- Contact donors via call or WhatsApp from search results
- Users can now propose new blood camps via the Blood Camp page; proposals await admin approval before appearing in the public list
- Admins may view all **registered participants** for each camp and contact them via call or WhatsApp using links in the admin panel

## Setup
1. Place project folder in your `htdocs` directory (already done).
2. Create a MySQL database using the provided `database.sql` script (`phpMyAdmin` or command line).
   ```sql
   source path/to/database.sql;
   ```
3. Update `config.php` if your database credentials differ.
4. Add actual images into the `images` folder (e.g., `donation1.jpg`, `donation2.jpg`, `donation3.jpg`, `college.jpg`).
   The homepage now features an attractive slider with full‑viewport images and navigation arrows; ensure filenames and extensions are correct.
5. Access the portal via `http://localhost/Blood_donation_portal/index.php`.
6. Admin login credentials (default): `admin` / `admin123` (change password after first login).

## Directory structure
```
Blood_donation_portal/
├── admin/
│   ├── dashboard.php
│   ├── login.php
│   ├── logout.php
│   └── manage_donors.php
├── css/
│   └── style.css
├── js/
│   └── scripts.js
├── images/
├── config.php
├── database.sql
├── index.php
├── register.php
├── find_donor.php
└── README.md
```

## Customization
- Adjust styles in `css/style.css` for branding
- Extend JavaScript validation in `js/scripts.js`
- Add blood donation awareness content or images in `index.php`
- Expand admin functionality as needed

Enjoy building and enhancing the portal!