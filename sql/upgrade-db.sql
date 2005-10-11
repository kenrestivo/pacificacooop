
--- do it

-- add to reports, enhancement-summry DONE?

alter table audit_trail add column     email_sent tinyint(1);
alter table audit_trail change column updated updated datetime NOT NULL;

