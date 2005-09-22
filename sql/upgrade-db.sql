-- HEREIT IS!

alter table job_descriptions add column free_tuition_start_month enum('January', 'Feburary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
 alter table job_descriptions add column free_tuition_end_month enum('January', 'Feburary', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

update  job_descriptions set free_tuition_start_month = 'September', free_tuition_end_month = 'June'  where free_tuition_months = 10;
update  job_descriptions set free_tuition_start_month = 'December', free_tuition_end_month = 'June'  where free_tuition_months = 6;

alter table job_descriptions drop column free_tuition_months;

