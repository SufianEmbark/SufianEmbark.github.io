# Classroom Reservation System

A web application for managing classroom and room reservations at universities, companies, or other organizations.  
Users can register, log in, browse available rooms, make reservations, and leave comments.  
Administrators can manage users, rooms, and monitor system activity through logs.

## Features
- User registration and authentication
- Room management (create, edit, delete)
- Reservation system with date and time slots
- Upload and display room photos
- Comment system for users
- Administrator dashboard with logs and configuration options
- Database management tools: restore from a backup, create a dump, or load the database with sample test data

## Project Setup Guide



### Prerequisites

Make sure the following software is installed on your system:

- Apache 2.4+
- PHP 8.x with PDO extension enabled
- MySQL or MariaDB (server and client)

On Ubuntu/Debian you can install everything with:

```
sudo apt update
sudo apt install apache2 php libapache2-mod-php php-mysql mysql-server
```


### Database Setup

1. Log into MySQL as root:
```sudo mysql -u root```

2. Create the database and user required by the project:

```
CREATE DATABASE IF NOT EXISTS sufianembark2425 CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
CREATE USER IF NOT EXISTS 'sufianembark2425'@'localhost' IDENTIFIED BY 'ueNg8ccuuLDCKUWc';
GRANT ALL PRIVILEGES ON sufianembark2425.* TO 'sufianembark2425'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```



### Deploying the Project

1. Copy the project folder into Apache’s web root:
```sudo cp -r proyectoFINAL /var/www/html/```

2. Ensure Apache has the right permissions:
```sudo chown -R www-data:www-data /var/www/html/proyectoFINAL```

3. Restart Apache:
```sudo systemctl restart apache2```


### Accessing the Application
Open your browser and go to:
<http://localhost/proyectoFINAL/>

On first run, the system will automatically create some required tables. If no administrator exists, it will insert a default one:
- **Email:** admin@void.ugr.es
- **Password:** admin

If you would like to load sample data into the database:
1. Log in as the administrator.
2. Go to the BBDD section.
3. Go to the section “Restaurar desde archivo .sql || Restore from .sql file”
4. Choose the file located at: /proyectoFINAL/bbdd/restaurar.sql
5. Click the “Restaurar || Restore” button.



### Troubleshooting
Access denied for user → Ensure you created the database and user exactly as shown above.

No such file or directory (MySQL socket) → Use 127.0.0.1 instead of localhost in db.php, or confirm MySQL is running with ```sudo systemctl status mysql```

Blank page / PHP errors → Check Apache’s error log:
```sudo tail -f /var/log/apache2/error.log```
