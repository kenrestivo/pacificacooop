#!/bin/sh
#$Id$
#move those .fixed temp files to their original names

for i in `ls *.fixed`; do
 	FOO=`echo "$i" | sed -e "s/\.fixed//"`
	 echo "moving $i to $FOO"
	mv "$i" "$FOO"
done