#!/bin/bash

cp -R /build/vendor/. ./vendor/
composer dump-autoload

exec "$@"
