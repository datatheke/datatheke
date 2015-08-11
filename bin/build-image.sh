#!/bin/sh

BUILD_DIR=`mktemp -d`
CURRENT_DIR=`pwd`

IMAGE_NAME="datatheke"
IMAGE_VERSION="latest"

# COPY ALL IN BUILD DIR & CHANGE DIR
cp -R . "${BUILD_DIR}"
cd "${BUILD_DIR}"

# CLEAN & PREPARE SOURCE
composer install --optimize-autoloader
rm -rf app/cache/* app/logs/* app/config/parameters.yml web/build web/config.php web/app_dev.php
mkdir web/uploads

# BUILD IMAGE
docker build -t "${IMAGE_NAME}:${IMAGE_VERSION}" -f docker/build/Dockerfile .

# GO BACK TO PREVIOUS DIR & REMOVE TEMP DIR
cd ${CURRENT_DIR}
rm -rf ${BUILD_DIR}
