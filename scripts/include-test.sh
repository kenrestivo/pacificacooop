#!/bin/sh

#$Id$

# checks for a narsty-ass pear include fuckup on the nfsn side

LOCAL="$1"

if [ "$LOCAL" ] ; then
	URL=http://www/coop-dev/tests/include-test.php 
else
	URL=http://www.pacificacoop.org/members/tests/include-test.php 
fi

RES=`lynx -dump $URL`

OK=`echo "$RES" | grep "OK"`

if [ "$OK" ]; then
	echo "includes OK"
else
	 echo "$RES no ay hose"
	echo "$RES at $URL" | mail -s "CO-OP INCLUDES ARE B0RKEN" ken@bc
	exit 1
fi

#EOF