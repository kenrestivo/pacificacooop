#$Id$
#makefile for sql databases

DBHOST=bc
MASTERHOST=bc
DBNAME=coop
TEMPFILE=/tmp/export.sql
DATE=${shell date +%Y.%m.%d.%H.%M.%S}
DATAFILE=coopbackup-data$(DATE)
DEFFILE=coopbackup-defs$(DATE)
TABS=cooptabs$(DATE)
TABLES=ins veh lic kids enrol parents kpglue

all::
	@echo "not so fast... get specific"
	@egrep "^[a-z]*:" Makefile | awk '{print $$1}' 

create::
	mysql -u ken -p -h $(DBHOST) mysql < definition.sql

dummy::
	mysql -u ken -p -h $(DBHOST) $(DBNAME) < dummydata.sql
	

reports::
	mysql -u ken -p -h $(DBHOST) $(DBNAME) < queries.sql

tabdump:
	mkdir  $(TABS); cd $(TABS)
	for i in $(TABLES); do \
		echo "===== exproting $$i=== " ; \
		mysql -B -e "select * from $$i" coop > $$i.txt ;\
	done


backup-defs::
	mysqldump \
		--add-drop-table \
		--all  \
		--database coop \
		--lock-tables \
		--no-data \
		-h $(DBHOST) -u ken -p > $(DEFFILE)
	bzip2 -f $(DEFFILE)

backup-data:
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

backup: backup-defs backup-data
	-mkdir backups/
	-mv *.bz2 backups/
		
#EOF

