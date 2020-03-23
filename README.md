# Iot Crawler App

```sh
docker-compose up -d
# @TODO: There must be a better way to do this …
docker-compose exec phpfpm chown -R daemon /app/var
docker-compose exec phpfpm composer install
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

## Assets

```sh
docker run --volume ${PWD}:/app --workdir /app node:latest yarn install
docker run --volume ${PWD}:/app --workdir /app node:latest yarn build
```

During development:

```sh
docker run --volume ${PWD}:/app --workdir /app --tty --interactive node:latest yarn watch
```

## Content pages

This project contains a very simple content management system. Go to
[http://0.0.0.0:8787/admin/?entity=Page](http://0.0.0.0:8787/admin/?entity=Page) to administer pages.

[http://0.0.0.0:8787/cms](http://0.0.0.0:8787/cms) shows the frontpage, i.e. the
first published page with no parent.

## Development

### Fixtures

Load all fixtures (will destroy all data):

```sh
docker-compose exec -e APP_ENV=dev phpfpm composer load-fixtures
```

Load a single fixture group:

```sh
docker-compose exec phpfpm bin/console doctrine:fixtures:load --group=experiment
``

### Generating sensor values

Generating a single value:

```sh
# Update (or create) a temperature measurement
# min value:      -20
# max value:       30
# max change (±):   1
docker-compose exec phpfpm bin/console app:measurement:add fixture:device:001 temperature -- -20 30 1
```

Continuously generating values:

```sh
docker-compose exec phpfpm bash -s <<<EOF
while true; do
  bin/console app:measurement:add fixture:device:001 humidity 0 100 10
  bin/console app:measurement:add fixture:device:001 temperature -- -20 30 1
  sleep 1 # second
done
EOF
```
### Coding standards

```sh
composer coding-standards-check
```

```sh
composer coding-standards-apply
```
