# Schedule & Attendance Information System (PHP Native)

This project is a simple web-based system for managing course schedules and attendance built using Native PHP and MySQL/MariaDB. This repository serves as a starter template, learning material, or a foundational boilerplate for academic management applications.

## 🛠️ Key Features

* **User Authentication**: Login & Logout session handling.
* **Master Data Management**:
  * CRUD for Lecturers (*Dosen*)
  * CRUD for Students (*Mahasiswa*)
  * CRUD for Courses (*Mata Kuliah*)
* **Schedule Management**: Configure course schedules, time slots, and room assignments.
* **Attendance Tracking**: Daily attendance entry and summary/recap generation.

---

## 📁 Project Directory Structure

```text
jadwal-absensi-tester/
├── absensi/              # Attendance entry & summary modules
├── assets/               # CSS, JS, and image assets
├── auth/                 # Login & logout pages
├── config/               # Database connection configuration
│   ├── database_example.php  # Database configuration template (tracked by Git)
│   └── database.php          # Local active configuration (ignored by Git / .gitignore)
├── database/             # SQL database dump files
│   └── jadwal_absensi.sql
├── dosen/                # Lecturer management module
├── includes/             # Reusable components (header, footer, auth checks, helpers)
├── jadwal/               # Course schedule module
├── mahasiswa/            # Student management module
├── mata_kuliah/          # Course management module
├── .gitignore            # Git ignore configuration
├── index.php             # Main dashboard page
└── README.md             # Project documentation
```

---

## 🚀 Installation & Local Setup Guide

Follow these steps to set up and run the project on your local environment (e.g., using XAMPP, WAMP, or Laragon):

### 1. Clone the Repository

Open your terminal or command prompt and run:

```bash
git clone https://github.com/IzumiZumi21/Example_Attendance-and-course-system_php-based.git
cd Example_Attendance-and-course-system_php-based
```

*(Or place the project folder directly into your local web server root directory, e.g., `C:/xampp/htdocs/jadwal-absensi-tester`).*

---

### 2. Import the Database

1. Open **phpMyAdmin** in your web browser (`http://localhost/phpmyadmin`).
2. Create a new database, for example named: `jadwal_absensi`.
3. Select the created database and navigate to the **Import** tab.
4. Choose the SQL file located inside the project repository:
   `database/jadwal_absensi.sql`
5. Click **Go** / **Import** to create the tables and seed initial data.

---

### 3. Configure Database Credentials

This project uses `config/database_example.php` as a template. You need to create your active local configuration file:

1. Navigate to the `config/` directory.
2. Duplicate `database_example.php` and rename the copy to `database.php`.
3. Open `config/database.php` and adjust the configuration to match your local setup:

```php
<?php
// config/database.php

$host = 'localhost';
$user = 'root';           // Change to your database username
$pass = '';               // Change to your database password
$db   = 'jadwal_absensi'; // Change to your database name

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
```

> **Note:** The `config/database.php` file is included in `.gitignore`, preventing your sensitive local credentials from being pushed to a public repository.

---

### 4. Run the Application

1. Ensure your Apache and MySQL servers are running in XAMPP/Laragon.
2. Open your web browser and navigate to:
   ```text
   http://localhost/jadwal-absensi-tester
   ```
   *(Adjust the URL path if your directory inside `htdocs` uses a different name).*

---

## 🔒 Security & Git Best Practices

* **Never Commit `database.php`**: Always commit `database_example.php` when pushing changes to GitHub.
* **Sanitize SQL Dumps**: Ensure `jadwal_absensi.sql` contains only dummy data and no sensitive credentials prior to pushing code.

---

## 📝 License & Contributing

This project is open-source and intended for educational and foundational development purposes. Feel free to fork the repository or submit pull requests for any enhancements.