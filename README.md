# Iot Crawler App

```sh
docker-compose up -d
docker-compose exec phpfpm composer install
# @TODO: There must be a better way to do this …
docker-compose exec phpfpm chown -R daemon /app/var
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

## Fixtures

```sh
docker-compose exec phpfpm bin/console doctrine:fixtures:load --no-interaction
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

```sh
docker-compose exec phpfpm bin/console doctrine:database:drop --force && \
docker-compose exec phpfpm bin/console doctrine:database:create && \
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction && \
docker-compose exec phpfpm bin/console doctrine:fixtures:load --no-interaction
```

### Coding standards

```sh
composer coding-standards-check
```

```sh
composer coding-standards-apply
```
