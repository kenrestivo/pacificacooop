#!/bin/sh

#$Id$
# nightly cron job to back up all of the key databases
# in the live version of the app


DATE=`/bin/date +%Y.%m.%d.%H.%M.%S`
BASEURL="http://www.pacificacoop.org/sf/export.php?command=backup&db="
SAVE=/mnt/kens/ki/is/c/coopinsurance/sql/backups
DBS="leads figlue inc"

/home/ken/bin/online on 

for i in $DBS ; do
	echo "----- backing up ${i} ------" >> $SAVE/coopbackup${DATE}.txt
	/usr/bin/lynx -source ${BASEURL}${i} >> $SAVE/coopbackup${DATE}.txt
done

/home/ken/bin/online off

