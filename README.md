Biospex
=======

Public Participation Manager

Requirements
------------

- Unbuntu
- Apache
- PHP => 5.6 ()
```sh
   sudo apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C
   sudo add-apt-repository -y ppa:ondrej/php5-5.6
   sudo apt-key update
   sudo apt-get update
```
- PHP Extensions
```sh
    sudo apt-get install -qq php5-cli php5-fpm php5-mysql php5-pgsql php5-sqlite php5-mongo php5-curl php5-gd php5-gmp php5-mcrypt php5-memcached php5-imagick php5-intl php5-xdebug
```
Memcached
```sh
    sudo apt-get install -qq memcached
```
Beanstalkd
```sh
    sudo apt-get install -qq beanstalkd
```
Supervisord
```sh
    sudo apt-get install -qq supervisor
```
ImageMagick
```sh
    sudo apt-get install imagemagick --fix-missing
```
Composer
```sh
    sudo curl -sS https://getcomposer.org/installer | sudo php
    sudo mv composer.phar /usr/local/bin/composer
```
Mongo DB
```sh
    sudo apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10
    echo "deb http://repo.mongodb.org/apt/ubuntu "$(lsb_release -sc)"/mongodb-org/3.0 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.0.list
    sudo apt-get update
    sudo apt-get install -y mongodb-org
```
MySql


Installation
------------

1. Clone the repo
2. Copy and rename ```.env.default.php``` to the environment (```.env.local.php```, ```.env.staging.php```, ```.env.php``` for production).
3. Set variables in ```*.env.php```.
5. Copy and rename ```/bootstrap/default.environment.php``` to ```environment.php``` and set your environment.
6. Run ```php composer.phar install```
7. Run the migrations: ```php artisan migrate```
8. Seed the Database: ```php artisan db:seed```

Notes
-----
1. Create biospex-queue.conf then read, update, and restart supervisord.
2. Add cron jobs for workflow manager (hourly), download clean (midnight), ocr polling.
  1. 0 * * * * /usr/bin/php /home/biospex/artisan workflow:manage >> /home/biospex/app/storage/logs/workflow.manage.log 2>&1
  2. 00 00 * * * /usr/bin/php /home/biospex/artisan download:clean >> /home/biospex/app/storage/logs/download.clean.log 2>&1
3. Add logrotate
  1. /etc/logrotate.d/apache2
```Nix
/home/biospex/app/storage/logs/*.log {
    daily
    missingok
    rotate 3
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    sharedscripts
    dateext
    dateformat -web01-%Y-%m-%d-%s
}
```
4. Edit /etc/default/beanstalkd and add or uncomment START=yes to start Beanstalkd upon server startup/reboot.

