#!/usr/bin/python

#insert into table_permissions (field_name, table_name, user_level, group_level)

#uses global c above
def has_school_year(j):
    c.execute("explain %s" % (j['table_name']))
    k=[i['Field'] for i in c.fetchall()]
    return k.count('school_year')

c.execute('select distinct table_name, realm_id from table_permissions')
l=filter(has_school_year, c.fetchall())

print """insert into table_permissions
(field_name, table_name, realm_id, user_level, group_level)
values"""
print ",\n".join(["('school_year', '%s', %d, 200, 200)" %
                  (i['table_name'], i['realm_id']) for i in l])
    
