#$Id$

#makefile for test case launcher tool. wacky.

UT=www
JAVABIN=/usr/local/java/bin
CLASSPATH=/usr/local/testcasetool/lib
FAKEPORT=9292
DATE=${shell date +%Y.%m.%d.%H.%M.%S}
HERE=${shell pwd}


all::
	@echo "not so fast... get specific"
	@egrep "^[a-z]*:" Makefile | awk '{print $$1}' 

record:
	$(JAVABIN)/java -classpath $(CLASSPATH)/webtest.jar \
		webtest.textui.TestCaseMaker 9292 $(UT)
	mv TestCase.csv record-$(DATE).csv

run:
	$(JAVABIN)/java -classpath $(CLASSPATH)/webtest.jar \
		webtest.textui.TestCaseRunner $(HERE) $(HERE)/reports

#EOF
