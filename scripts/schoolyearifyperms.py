#!/usr/bin/python

#insert into table_permissions (field_name, table_name, user_level, group_level)

#uses global c above
def has_school_year(j):
    c.execute("explain %s" % (j))
    k=[i['Field'] for i in c.fetchall()]
    return k.count('school_year')

c.execute('show tables')
l=[i['Tables_in_coop'] for i in c.fetchall()]

print "insert into table_permissions (field_name, table_name, user_level, group_level) values"
print ",\n".join(["('school_year', %s, 200, 200)" % (i) for i in
                  filter(has_school_year,l)])
    
