-- HEREIT IS!


alter table audit_trail add column details longtext;
alter table kids add column doctor_id int(32);

--- add bring baby table (definition.sql)

insert into brings_baby 
(baby_due_date, baby_too_old_date, worker_id)  
(select '2005-09-12', '2005-06-12', worker_id 
from workers 
where brings_baby > 0);


-- run the doctor import (importrasta.py)