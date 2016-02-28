#!/bin/sh
BASEDIR=$(dirname $0)
cd $BASEDIR
php teams.php $1 $2
