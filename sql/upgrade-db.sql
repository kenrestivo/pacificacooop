-- HEREIT IS!


alter table audit_trail add column details longtext;
alter table kids add column doctor_id int(32);

--- add bring baby table (definition.sql)

insert into brings_baby 
(baby_due_date, baby_too_old_date, worker_id)  
(select '2005-09-12', '2005-06-12', worker_id 
from workers 
where brings_baby > 0);

alter table workers drop column brings_baby;

-- run the doctor import (importrasta.py)

update leads set salutation = 'Dr.' where lead_id in 
(select doctor_id from kids where doctor_id > 0);

-- add realm for bring_baby: membership

-- make leads 600/600/500. this is gonna be a wild ride

-- permissions for allergies and doctor fields: group 500

-- add enrollment_summary report to reports db (membership realm)

