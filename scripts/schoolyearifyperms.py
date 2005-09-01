#!/usr/bin/python

#insert into table_permissions (field_name, table_name, user_level, group_level)

#uses global c above
def has_field(f,j):
    c.execute("explain %s" % (j['table_name']))
    k=[i['Field'] for i in c.fetchall()]
    return k.count(f)


#### missing schoolyears
c.execute('select distinct table_name, realm_id from table_permissions')
l=filter(lambda x: has_field('school_year', x), c.fetchall())

print """insert into table_permissions
(field_name, table_name, realm_id, user_level, group_level)
values"""
print ",\n".join(["('school_year', '%s', %d, 200, 200)" %
                  (i['table_name'], i['realm_id']) for i in l])
    


### ok, now time for the family stuff too
c.execute("""select sum(if(field_name = 'family_id', 1, 0)) as fampresent, 
realm_id, table_name 
from table_permissions  
group by table_name 
having fampresent <1 
order by table_name""")
l=filter(lambda x: has_field('family_id', x), c.fetchall())
print ",\n".join(["('family_id', '%s', %d, 200, 200)" %
                  (i['table_name'], i['realm_id']) for i in l])

