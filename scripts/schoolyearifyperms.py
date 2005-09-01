#!/usr/bin/python

#insert into table_permissions (field_name, table_name, user_level, group_level)

c.execute('show tables')
t=c.fetchall()
l=[i['Tables_in_coop'] for i in t]

print "insert into table_permissions (field_name, table_name, user_level, group_level) values"

print ",\n".join(["('school_year', %s, 200, 200)" % (i) for i in l])
    
