#!/usr/bin/python

#a silly script to fix my users and groups

#do the c = cursor shit first


#the parents
c.execute('select user_id from users where family_id > 0')
r=c.fetchall()
for j in [int(i['user_id']) for i in r]:
      c.execute("insert into users_groups_join set group_id = 1, user_id = %d" % (j))

#the teachers
c.execute('select user_id from users where family_id < 1')
r=c.fetchall()
for j in [int(i['user_id']) for i in r]:
      c.execute("insert into users_groups_join set group_id = 2, user_id = %d" % (j))
