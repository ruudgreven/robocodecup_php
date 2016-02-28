#!/bin/sh
BASEDIR=$(dirname $0)
cd $BASEDIR
php pools.php $1 $2
