#$Id$

#makefile for test case launcher tool. wacky.

UT=www
JAVABIN=/usr/local/java/bin
CLASSPATH=/usr/local/testcasetool/lib
FAKEPORT=9292
DATE=${shell date +%Y.%m.%d.%H.%M.%S}
HERE=plans/


all::
	@echo "not so fast... get specific"
	@egrep "^[a-z]*:" Makefile | awk '{print $$1}' 

record:
	$(JAVABIN)/java -classpath $(CLASSPATH)/webtest.jar \
		webtest.textui.TestCaseMaker 9292 $(UT) \
		80 "" "" "html,pl,php,php3,php4" recordings/record-$(DATE).csv

run:
	$(JAVABIN)/java -classpath $(CLASSPATH)/webtest.jar \
		webtest.textui.TestCaseRunner $(HERE) reports/

#EOF
