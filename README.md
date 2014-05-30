Biospex
=======

Public Participation Manager

Requirements
------------

 - PHP 5.4 or greater
 - MySQL
 - Mongodb
 - Composer
 - Mongo PECL Extension
 - PHP GD library
 - PHP mcrypt

Installation
------------

1. Clone the repo
2. Run ```php composer.phar update```
3. Set up MySql and Mongo database configuration in ```app/config/database.php```
4. Edit ```app/config/mail.php``` to work with your mail setup.
5. Run the migrations: ```php artisan migrate```
6. Seed the Database: ```php artisan db:seed```
