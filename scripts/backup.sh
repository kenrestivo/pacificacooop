#!/bin/sh

#$Id$
# nightly cron job to back up all of the key databases
# in the live version of the app


DBS="leads figlue inc faglue auction users groups privs families parents kids attendance"
#SAVE=../sql/backups
SAVE=/mnt/kens/ki/is/c/coopinsurance/sql/backups
BASEURL="http://www.pacificacoop.org/sf/export.php?command=backup&db="
DATE=`/bin/date +%Y.%m.%d.%H.%M.%S`

mkdir -p ${SAVE}

for i in $DBS ; do
	echo "----- backing up ${i} ------" >> $SAVE/coopbackup${DATE}.txt
	/usr/bin/lynx -source ${BASEURL}${i} >> $SAVE/coopbackup${DATE}.txt
done


#EOF
