#!/usr/bin/env bash

DIR="$( cd "$(dirname $( dirname "${BASH_SOURCE[0]}" ) )" >/dev/null && pwd )"

$DIR/vendor/bin/wp --allow-root --path=$DIR/webroot/wp cron event run --due-now
