# karma8-test

## Before run
make copy of dist files:
```
cp .env.dist .env
```

```
cp docker-compose.yml.dist docker-compose.yml
```

## Staring docker containers
```
docker-compose up -d
```

## Installing dependencies
```
docker-compose exec cron composer install
docker-compose restart
```

## Adding fake data to DB
```
docker-compose exec cron php -f src/add_fake_data.php
```

## Main containers description
### karma8-test.cron.php
used to run single cron task every minute to check users with expiring subscriptions needed to notify
```
* * * * * root php -f /var/www/src/sender.php  >> /var/log/cron.log 2>&1
```
php script located in `src/sender.php` file

### karma8-test.consumer.php
message consumer which receives messages about users which needed to send notification
php script located in `src/consumer.php` file
