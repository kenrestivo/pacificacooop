#!/bin/sh

for i in `seq 2  35`; do echo "insert into coop.keglue set kidsid = $i, enrolid = 1"; done  > kidsglue.sql

  for i in `seq 36 73`; do echo "insert into coop.keglue set kidsid = $i, enrolid = 2"; done  >> kidsglue.sql

mysql coop < kidsglue.sql
