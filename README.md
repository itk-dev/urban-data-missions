# Iot Crawler App

## Installation

Run `./scripts/deploy dev` for development or just `./scripts/deploy` for
production – or do it manually:

```sh
docker-compose up -d
docker-compose exec phpfpm composer install
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
# @TODO: There must be a better way to do this …
docker-compose exec phpfpm chown -R daemon /app/var
```

## Translations

```sh
# Remember to set the DEFAULT_LOCALE environment variable so the XLF-files will have the correct source-language
docker-compose exec -e DEFAULT_LOCALE=en phpfpm bin/console translation:update --force da
# Mark default translations as “Needs work”.
sed -i '' 's/\<target\>__/\<target state="needs-l10n"\>__/' translations/*.xlf
# Dump JavaScript translations
docker-compose exec phpfpm bin/console bazinga:js-translation:dump assets/ --format=json
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

With [Mutagen](https://mutagen.io/):

```sh
mutagen project start
```

### Fixtures

Load all fixtures (will destroy all data):

```sh
docker-compose exec -e APP_ENV=dev phpfpm composer load-fixtures
```

Load a single fixture group:

```sh
docker-compose exec phpfpm bin/console hautelook:fixtures:load --group=experiment
``

### Generating sensor values

Generating a single value:

```sh
# Update (or create) a temperature measurement
# min value:      -20
# max value:       30
# max change (±):   1
docker-compose exec phpfpm bin/console app:measurement:add sensor-001 temperature -- -20 30 1
```

Continuously generating values:

```sh
docker-compose exec phpfpm bash -s <<<EOF
while true; do
  bin/console app:measurement:add sensor-001 humidity 0 100 10
  bin/console app:measurement:add sensor-001 temperature -- -20 30 1
  sleep 1 # second
done
EOF
```
### Coding standards

```sh
composer coding-standards-check
yarn coding-standards-check
```

```sh
composer coding-standards-apply
yarn coding-standards-apply
```


## Mercure

https://github.com/dunglas/mercure
https://github.com/dunglas/mercure/releases
