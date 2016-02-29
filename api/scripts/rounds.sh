#!/bin/sh
BASEDIR=$(dirname $0)
cd $BASEDIR
php rounds.php $1 $2 $3 $4
