#!/bin/bash

CHECKSUM_FILE=".dockerfile_checksum"
IMAGE_NAME="cached-mapper-bundle/php:latest"

NEW_CHECKSUM=$(sha256sum Dockerfile | cut -d " " -f 1)
if [ -f $CHECKSUM_FILE ]; then
    OLD_CHECKSUM=$(cat $CHECKSUM_FILE)
else
    OLD_CHECKSUM=""
fi

if [ "$NEW_CHECKSUM" != "$OLD_CHECKSUM" ]; then
    echo "Dockerfile has changed. Removing old image."
    echo $NEW_CHECKSUM > $CHECKSUM_FILE
    docker image rm -f $IMAGE_NAME > /dev/null 2>&1
fi

IMAGE_EXISTS=$(docker images -q $IMAGE_NAME)

if [ -z "$IMAGE_EXISTS" ]; then
    echo "Image does not exist. Building image."
    docker build -t $IMAGE_NAME .
else
    echo "Image exists. Using existing image."
fi

docker rm -f php > /dev/null 2>&1

if [ ! -f .env.local ]; then
    cp .env.local.dist .env.local;
    echo "Created .env.local based on .env.local.dist";
fi

docker run -d --name php \
    --env-file .env.local \
    -v $(pwd):/app \
    -w /app \
    $IMAGE_NAME bash -c "tail -f /dev/null"

echo -e "Container started. Run \033[33;1mdocker exec -it php bash\033[0m to enter container or \033[33;1mdocker exec php composer install\033[0m to install dependencies.";
