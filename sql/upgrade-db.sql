-- HEREIT IS!

insert into territories set territory_id = 8, description = 'No Territory Assigned';
update companies set territory_id = 8 where territory_id is null or territory_id < 1;
alter table territories drop column school_year;


