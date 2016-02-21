#!/bin/sh
BASEDIR=$(dirname $0)
cd $BASEDIR
php runbattleconfiguration.php $1 $2
