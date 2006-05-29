#!/usr/bin/python


import csv

f=open('/tmp/foobar.txt', 'w')
w=csv.writer(f)
w.writerow(['foo', 'bar', 'baz'])
w.writerow(['foo', 'bar', 'baz ya func'])
w.writerow(["foo's ball", '"bar"', 'baz ya func'])
w.writerow([100, 200, 'hey, this rules'])
f.close
