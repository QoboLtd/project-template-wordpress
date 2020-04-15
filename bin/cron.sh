# @Author: sotirisky
# @Date:   2020-03-31 09:45:10
# @Last Modified by:   sotirisky
# @Last Modified time: 2020-03-31 09:46:03

#!/usr/bin/env bash

DIR="$( cd "$(dirname $( dirname "${BASH_SOURCE[0]}" ) )" >/dev/null && pwd )"

php $DIR/webroot/wp/wp-cron.php
