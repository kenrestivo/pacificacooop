#!/usr/bin/python

#a silly script to fix my users and groups

#do the c = cursor shit first


#the parents
c.execute('select user_id from users where family_id > 0')
for j in [int(i['user_id']) for i in c.fetchall()]:
      c.execute("insert into users_groups_join set group_id = 1, user_id = %d" % (j))

#the teachersa
c.execute('select user_id from users where family_id < 1')
for j in [int(i['user_id']) for i in c.fetchall()]:
      c.execute("insert into users_groups_join set group_id = 2, user_id = %d" % (j))
