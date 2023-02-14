FROM php:8.1.4

WORKDIR /var/www

RUN apt-get update
RUN apt-get install -y libicu-dev
RUN apt-get install -y libpq-dev
RUN apt-get install -y git
RUN apt-get install -y zip
RUN apt-get install -y libzip-dev

RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_pgsql

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer --version=2.2.5

RUN apt-get update && apt-get install -y librabbitmq-dev libssh-dev \
    && docker-php-ext-install bcmath sockets \
    && pecl install amqp \
    && docker-php-ext-enable amqp

RUN apt-get install -y cron
RUN echo "* * * * * root php -f /var/www/src/sender.php >> /var/log/cron.log 2>&1" >> /etc/crontab
RUN chmod 0644 /etc/crontab
RUN crontab /etc/crontab
RUN touch /var/log/cron.log