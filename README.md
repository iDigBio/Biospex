Biospex
=======

Public Participation Manager

Requirements
------------

 - PHP 5.5 or greater
 - MySQL
 - Mongodb
 - Composer
 - Mongo PECL Extension
 - PHP gd, imagick, mcrypt, mysql, mysqlnd, opcache, memcached, pdo, pdo_mysql, pdo_sqlite,
 readline, sqlite3


Installation
------------

1. Clone the repo
2. Run ```php composer.phar install```
3. Set up MySql and Mongo database configuration in ```app/config/database.php```
4. Edit ```app/config/mail.php``` to work with your mail setup.
5. Run the migrations: ```php artisan migrate```
6. Seed the Database: ```php artisan db:seed```

Notes
-----
1. Add cron jobs for subject import and workflow manager
  1. 0 * * * * /usr/bin/php /home/biospex/artisan subject:import >> /home/biospex/app/storage/logs/subject.import.log 2>&1
  2. 0 * * * * /usr/bin/php /home/biospex/artisan workflow:manage >> /home/biospex/app/storage/logs/workflow.manage.log 2>&1
2. Add logrotate
  1. /etc/logrotate.d/subject_import
```Nix
/home/robert/Work/biospex/app/storage/logs/subject.import.log {
    daily
    rotate 5
    compress
    delaycompress
    missingok
    notifempty
    copytruncate
}
```
  2. /etc/logrotate.d/workflow_manager
```Nix
/home/robert/Work/biospex/app/storage/logs/workflow.manage.log {
    daily
    rotate 5
    compress
    delaycompress
    missingok
    notifempty
    copytruncate
}
```




