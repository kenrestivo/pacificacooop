#$Id$
#makefile for sql databases

DBHOST=bc
MASTERHOST=bc
DBNAME=coop
TEMPFILE=/tmp/export.sql
DATE=${shell date +%Y.%m.%d.%H.%M.%S}
DATAFILE=coopbackup$(DATE)

all::
	@echo "not so fast... get specific"
	@egrep "^[a-z]*:" Makefile | awk '{print $$1}' 

create::
	mysql -u ken -p -h $(DBHOST) mysql < definition.sql

dummy::
	mysql -u ken -p -h $(DBHOST) $(DBNAME) < dummydata.sql
	

reports::
	mysql -u ken -p -h $(DBHOST) $(DBNAME) < queries.sql

backup:
	mysqldump \
		--add-locks \
		--all  \
		--database coop \
		--extended-insert \
		--lock-tables \
		--no-create-db \
		--no-create-info \
		--quick \
		-h $(DBHOST) -u ken -p > $(DATAFILE)
	bzip2 -f $(DATAFILE)

		
#EOF

