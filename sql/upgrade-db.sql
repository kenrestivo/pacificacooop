
--- do it

-- add to reports, enhancement-summry

alter table audit_trail add column     email_sent tinyint(1);
update audit_trail set email_sent = 1;


