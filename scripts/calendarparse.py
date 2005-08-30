#!/usr/bin/python

# 	$Id$	

f=open('/mnt/kens/ki/proj/coop/imports/PCNS2005-2006Calendar.txt', 'r')
l=f.readlines()
f.close()

lsp=[filter(lambda x: x!= '', s.strip('\n').split('\t')) for s in l]
act=[x[1] for x in filter(lambda x: len(x) > 1, lsp)]

