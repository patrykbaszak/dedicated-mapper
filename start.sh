#!/bin/bash

sudo chown -R $USER .

docker-compose down --volumes --remove-orphans

docker-compose up -d 

rm -rf var/cache

sudo chown -R $USER .

docker-compose exec app composer cache:clear
