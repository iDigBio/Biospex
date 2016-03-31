Biospex
=======

Public Participation Manager

Requirements
------------

 - PHP 5.6 or greater
 - MySQL
 - Mongodb
 - Composer
 - Mongo PECL Extension
 - PHP sudo apt-get install -qq php5-cli php5-fpm php5-mysql php5-pgsql php5-sqlite php5-mongo php5-curl php5-gd php5-gmp php5-mcrypt php5-memcached php5-imagick php5-intl php5-xdebug
 - Beanstalkd
 - Supervisord
 - Mailgun or some other email configuration
 - Redis: sudo apt-get install redis-server
 - PHPRedis: sudo apt-get install php5-redis
 - NPM: npm install express ioredis socket.io --save


Installation
------------

1. Clone the repo
2. Copy and rename ```.env.example``` to ```.env```.
3. Set variables in ```.env```.
6. Run ```php composer.phar install```
7. Run the migrations: ```php artisan migrate```
8. Seed the Database: ```php artisan db:seed```

Notes
-----
1. Create biospex-queue.conf in /etc/supervisor/config using biospex-queue.conf file as an example. Then read, update, and restart supervisord.
2. Add cron jobs for workflow manager (hourly), download clean (midnight), ocr polling.
  1. 0 * * * * /usr/bin/php /home/biospex/artisan workflow:manage >> /home/biospex/app/storage/logs/workflow.manage.log 2>&1
  2. 00 00 * * * /usr/bin/php /home/biospex/artisan download:clean >> /home/biospex/app/storage/logs/download.clean.log 2>&1
3. Edit /etc/default/beanstalkd and add or uncomment START=yes to start Beanstalkd upon server startup/reboot.

