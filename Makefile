#$Id$

#mkefile overalll

PWD=$(shell pwd)

all::

slo:
	sloccount --addlangall \
		$(PWD)/web  $(PWD)/scripts $(PWD)/gtkcoop  $(PWD)/sql \
		$(PWD)/qa
