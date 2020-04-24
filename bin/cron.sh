#!/usr/bin/env bash

DIR="$( cd "$(dirname $( dirname "${BASH_SOURCE[0]}" ) )" >/dev/null && pwd )"

php $DIR/webroot/wp/wp-cron.php
