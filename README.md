# symfony_demo

1) clone project and cd into symfony_demo
2) `composer install`
3) fill out db string in .env.local eg DATABASE_URL=mysql://root:root@127.0.0.1:8889/url?serverVersion=5.7 then run
`php bin/console doctrine:database:create`
4) fill out db string in .env.test.local eg DATABASE_URL=mysql://root:root@127.0.0.1:8889/url_test?serverVersion=5.7
then run `php bin/console doctrine:database:create --env=test`
5) `php bin/console doctrine:migrations:migrate`
6) `yarn add bootstrap --dev`
7) `yarn add @symfony/webpack-encore --dev`
8) `yarn add webpack-notifier@^1.6.0 --dev`
9) `yarn run encore dev`
10) `./bin/phpunit` which will also run the migration for the test db
11) `symfony serve` to run

