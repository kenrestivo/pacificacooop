#!/bin/sh

#$Id$
#


DATE=`/bin/date +%Y.%m.%d.%H.%M.%S`

/home/ken/bin/online on 

/usr/bin/lynx -source 'http://www.pacificacoop.org/sf/export.php?command=backup&db=leads' > /mnt/kens/ki/is/c/coopinsurance/sql/backups/coopbackup${DATE}.txt

/home/ken/bin/online off

