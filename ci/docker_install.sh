#!/usr/bin/env bash

# We need to install dependencies only for Docker
[[ ! -e /.dockerenv ]] && [[ ! -e /.dockerinit ]] && exit 0

set -xe

apt-get update -yqq
apt-get install git wget zlib1g-dev zlibc zlib1g zlib1g-dev libicu-dev g++ libzip4 libzip-dev unzip -yqq

docker-php-ext-install zip > /dev/null
pecl install xdebug > /dev/null
