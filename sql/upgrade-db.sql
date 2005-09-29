
--- do it

-- add subscriptions realm
-- add subscriptions table WITH DELETE PERMS (my perms are TOTALLY fucked up)
-- and group privs for subscriptions


alter table job_descriptions change column 	free_tuition_start_month free_tuition_start_month enum('None', 'January', 'Feburary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') default 'None';


 alter table job_descriptions change column 	free_tuition_end_month free_tuition_end_month enum('None', 'January', 'Feburary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') default 'None';



