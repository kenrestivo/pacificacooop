#!/usr/bin/python

#insert into table_permissions (field_name, table_name, user_level, group_level)

#uses global c above

def has_field(f,j):
    c.execute("explain %s" % (j['table_name']))
    k=[i['Field'] for i in filter(lambda x: not x['Key'], c.fetchall())]
    return k.count(f)

def addDefaultPerms(field, user, group):
    c.execute("""
select sum(if(field_name = '%s', 1, 0)) as present, 
realm_id, table_name 
from table_permissions  
group by table_name, realm_id
having present <1 
order by table_name""" % (field))
    l=filter(lambda x: has_field(field, x), c.fetchall())
    print ",\n".join(["('%s', '%s', %d, %d, %d)" %
                      (field, i['table_name'], i['realm_id'], user, group)
                      for i in l])


print """INSERT INTO table_permissions
(field_name , table_name,  realm_id , 
user_level , group_level)
values"""


### heh, didn't i have a huge perl script to do stuff like this once?
addDefaultPerms('school_year', 200,200)
addDefaultPerms('family_id', 200,200)


# misc shit
#for i in ['table_permissions', 'report_permissions', 'user_privileges'] : print 'alter table %s add column year_level int(5) default NULL;' %(i) 
