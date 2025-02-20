# Library management system

This project is a library management system that integrates a PostgreSQL database and a PHP-based web application designed for both librarians and patrons.

The documentation, which includes the project's specifications, is available [here](https://midi-file-e7c.notion.site/Library-management-system-16bc3dc3fb3780d8bf56c92189c1079b).

## Running locally

### 0. Requirements
1. **PHP**: version 8.3 or higher.
2. **PostgreSQL**: version 17 or higher.
3. **Web Server** of choice (PHP 5.4 and later have a built-in web server).

### 1. Clone the repository
```zsh
git clone https://github.com/lechemi/library.git
cd library
```

### **2. Set up the database**
1. Ensure that your PostgreSQL server is running.

2. Create a new database to hold the library data:
   ```zsh
   createdb -U postgres library
   ```

3. Restore the database dump:
   ```zsh
   psql -U postgres -d library -f sql/dump/dump.sql
   ```

4. Log into the database:
   ```zsh
   psql -U postgres -d library
   ```

5. Set the search path:
   ```zsh
   SET search_path TO library;
   ```

6. Check if the tables and data have been successfully restored:
   ```zsh
   \dt
   ```

### 3. Configure database connection details
Edit `webapp/conf/conf.php` to set the database connection details.

### 4. Run the web app
(example with PHP built-in server).

While being in the `webapp` folder, run:
```zsh
php -S 127.0.0.1:8000
```
Then, navigate to http://127.0.0.1:8000/. The app should be up and running.
