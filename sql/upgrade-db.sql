--

-- add table permisssions: auction stuff to System realm

-- add access levels to system realm?


-- aha oh ho. nag indulgences privs are fuxored
-- HOW? go fix.


-- springfest chairs (and erin, and petra) past tickets


-- add sponsorshiptypes to the perms table, admin only, then test it out please

-- add sources

-- run the remove query? WHAT remove query?



-- ignore for paddles??
-- UPDATE table_permissions SET field_name = NULL ,
-- group_level = -1 WHERE table_permissions.table_permissions_id = 54;

--  reanimate invitations
insert into invitations 
(school_year, relation, lead_id)
values
('2005-2006', 'Alumni', 666),
('2005-2006', 'Alumni',1189),
('2005-2006', 'Alumni',667),
('2005-2006', 'Alumni',670),
('2005-2006', 'Alumni',672),
('2005-2006', 'Alumni',675),
('2005-2006', 'Alumni',1190),
('2005-2006', 'Alumni',684),
('2005-2006', 'Alumni',692),
('2005-2006', 'Alumni',699),
('2005-2006', 'Alumni',1191),
('2005-2006', 'Alumni',716),
('2005-2006', 'Alumni',719),
('2005-2006', 'Alumni',727),
('2005-2006', 'Alumni',1192),
('2005-2006', 'Alumni',735),
('2005-2006', 'Alumni',740),
('2005-2006', 'Alumni',746),
('2005-2006', 'Alumni',750),
('2005-2006', 'Alumni',752),
('2005-2006', 'Alumni',754),
('2005-2006', 'Alumni',1173),
('2005-2006', 'Alumni',1194),
('2005-2006', 'Alumni',1174),
('2005-2006', 'Alumni',1175),
('2005-2006', 'Alumni',785),
('2005-2006', 'Alumni',788),
('2005-2006', 'Alumni',790),
('2005-2006', 'Alumni',793),
('2005-2006', 'Alumni',796),
('2005-2006', 'Alumni',800),
('2005-2006', 'Alumni',802),
('2005-2006', 'Alumni',1197),
('2005-2006', 'Alumni',806),
('2005-2006', 'Alumni',811),
('2005-2006', 'Alumni',1198),
('2005-2006', 'Alumni',821),
('2005-2006', 'Alumni',831),
('2005-2006', 'Alumni',832),
('2005-2006', 'Alumni',1199),
('2005-2006', 'Alumni', 1177),
('2005-2006', 'Alumni',850),
('2005-2006', 'Alumni',864),
('2005-2006', 'Alumni',865),
('2005-2006', 'Alumni',1179),
('2005-2006', 'Alumni',1201),
('2005-2006', 'Alumni',873),
('2005-2006', 'Alumni',1202),
('2005-2006', 'Alumni',1180),
('2005-2006', 'Alumni',1728),
('2005-2006', 'Alumni',1204),
('2005-2006', 'Alumni',914),
('2005-2006', 'Alumni',1210),
('2005-2006', 'Alumni',919),
('2005-2006', 'Alumni',920),
('2005-2006', 'Alumni',927),
('2005-2006', 'Alumni',1174),
('2005-2006', 'Alumni',1182),
('2005-2006', 'Alumni',932),
('2005-2006', 'Alumni',935),
('2005-2006', 'Alumni',1206),
('2005-2006', 'Alumni',1207),
('2005-2006', 'Alumni',944),
('2005-2006', 'Alumni',947),
('2005-2006', 'Alumni',952),
('2005-2006', 'Alumni',953),
('2005-2006', 'Alumni',963),
('2005-2006', 'Alumni',971),
('2005-2006', 'Alumni',1209),
('2005-2006', 'Alumni',986),
('2005-2006', 'Alumni',995),
('2005-2006', 'Alumni',996),
('2005-2006', 'Alumni',1007),
('2005-2006', 'Alumni',1009),
('2005-2006', 'Alumni',1013),
('2005-2006', 'Alumni',1018),
('2005-2006', 'Alumni',1019),
('2005-2006', 'Alumni',1029),
('2005-2006', 'Alumni',1041),
('2005-2006', 'Alumni',1186),
('2005-2006', 'Alumni',1187),
('2005-2006', 'Alumni',1188),
('2005-2006', 'Alumni', 1067)
;

select count(invitation_id) 
from invitations 
where (label_printed  is null or label_printed < "2000-01-01") and 
(family_id is null or family_id < 1);

--- trujillo address



