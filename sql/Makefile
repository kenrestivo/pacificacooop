#$Id$
#makefile for sql databases

DBHOST=bc
MASTERHOST=bc
DBNAME=coop
TEMPFILE=/tmp/export.sql

all::
	@echo "not so fast... get specific"
	@egrep "^[a-z]*:" Makefile | awk '{print $$1}' 

status::
	mysql -u ken -p -h $(DBHOST) mysql < definition.sql

dummy::
	mysql -u ken -p -h $(DBHOST) $(DBNAME) < dummydata.sql
	

reports::
	mysql -u ken -p -h $(DBHOST) $(DBNAME) < queries.sql

		

#EOF

