# Accreditation System

A lightweight PHP (PDO) + MySQL accreditation tracker.

## Quick start
1. Import the SQL from `DATABASE/accred.sql` into your MySQL/MariaDB database.
2. Create a `db.env` file in the project root and set:
   ```
   DB_HOST=localhost
   DB_NAME=your_db_name
   DB_USER=your_db_user
   DB_PASS=your_db_password
   ```
3. Deploy the whole folder to your host. Visit `PHP/login.php` to sign in.

## Notes
- DB connection is centralized in `PHP/db.php` and reads from `db.env`/environment.
- File uploads live in `uploads/` (execution disabled by `.htaccess`).
- Frontend assets are in `app/` (vanilla JS + CSS).
