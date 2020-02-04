# Iot Crawler App

Based on

* https://github.com/dunglas/symfony-docker
* https://github.com/ScorpioBroker/ScorpioBroker
* https://www.etsi.org/deliver/etsi_gs/CIM/001_099/009/01.01.01_60/gs_CIM009v010101p.pdf

```sh
docker-compose up -d
docker-compose exec phpfpm composer install
# @TODO: There must be a better way to do this …
docker-compose exec phpfpm chown -R daemon /app/var
docker-compose exec phpfpm bin/console doctrine:migrations:migrate --no-interaction
```

## Fixtures

```sh
docker-compose exec phpfpm bin/console doctrine:fixtures:load --no-interaction --group=iot-data
docker-compose exec phpfpm bin/console doctrine:fixtures:load --no-interaction --group=experiment
```

Fake some measurements

```sh
docker-compose exec phpfpm bin/console app:measurement update fixture:sensor:001:temperature temperature 1
```


Experiment: http://0.0.0.0:8787/experiment/
Scorpio Broker: http://0.0.0.0:9090/ngsi-ld/v1/entities/

## Measurements

http://0.0.0.0:9090/ngsi-ld/v1/entities/?type=https%3A%2F%2Furi.fiware.org%2Fns%2Fdata-models%23temperature
http://0.0.0.0:9090/ngsi-ld/v1/entities/?type=https%3A%2F%2Furi.fiware.org%2Fns%2Fdata-models%23humidity



https://gitlab.iotcrawler.net/core/iotcrawler_core/snippets/5

https://gitlab.iotcrawler.net/core/iotcrawler_core#deployed-components


## Creating measurements

```sh
docker-compose exec phpfpm bin/console app:measurement create sensor:test087 temperature 42
docker-compose exec phpfpm bin/console app:measurement update sensor:test087 temperature 43
docker-compose exec phpfpm bin/console app:measurement update sensor:test087 temperature 40 --measured-at='-1 hour'
```
