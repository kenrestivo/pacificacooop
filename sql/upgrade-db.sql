
--- do it

-- the massive import of names.

alter table leads drop column school_year;

delete from table_permissions where field_name in ('school_year', 'family_id');