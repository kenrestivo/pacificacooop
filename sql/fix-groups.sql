--one-shot deal

-- parents
insert into users_groups_join (group_id, user_id) 
select 1, user_id from users where family_id > 0

-- teachers
insert into users_groups_join (group_id, user_id) 
select 2, user_id from users where family_id < 1
