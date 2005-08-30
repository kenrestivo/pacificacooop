#!/usr/bin/python

# 	$Id$	


months={'january':1, 'february':2, 'march':3, 'april':4,'may':5,
        'june':6, 'july':7, 'august':8, 'september':9, 'october':10,
        'november':11, 'december':12}




f=open('/mnt/kens/ki/proj/coop/imports/PCNS2005-2006Calendar.txt', 'r')
l=f.readlines()
f.close()

lsp=[filter(lambda x: x!= '', s.strip('\n').split('\t')) for s in l]
act=[x[1] for x in filter(lambda x: len(x) > 1, lsp)]

