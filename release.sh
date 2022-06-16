#!/bin/bash

cwd=`pwd`
VERSION=`grep Version: power-form-7.php | awk '{print $3}'`
composer install --no-dev
cd .. && zip -r power-form-7/power-form-7-$VERSION.zip power-form-7 -x '*/TODO' -x '*/node_modules*' -x '*/.git*' -x '*/.vscode*' -x '*/*.sh' -x '*/*.zip'
cd $cwd
