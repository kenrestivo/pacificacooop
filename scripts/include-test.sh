#!/bin/sh

#$Id$

# checks for a narsty-ass pear include fuckup on the nfsn side

URL=http://www/coop-dev/include-test.php 
#URL=http://www.pacificacoop.org/members/include-test.php 

RES=`lynx -dump $URL`

OK=`echo "$RES" | grep "OK"`

if [ "$OK" ]; then
	echo "includes OK"
else
	 echo "$RES no ay hose"
	echo "$RES" | mail -s "CO-OP INCLUDES ARE B0RKEN" ken@bc
fi

#EOF