#!/bin/sh
BASEDIR=$(dirname $0)
cd $BASEDIR
php battles.php $1 $2 $3 $4
