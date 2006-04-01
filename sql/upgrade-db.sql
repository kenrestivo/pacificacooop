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



--- POLLS
-- add votes/question/answer tables (definition)
 INSERT INTO realms (short_description , meta_realm_id ) VALUES
('Polls' , 0 );

INSERT INTO user_privileges (user_id , group_id ,
user_level , group_level , realm_id , year_level , menu_level ) VALUES 
( 0 , 3 , 600 , 200 , 25 , 200 , -1 );
INSERT INTO user_privileges (user_id , group_id ,
user_level , group_level , realm_id , year_level , menu_level ) VALUES 
( 0 , 1 , 600 , 0 , 25 , 200 , -1 );



-- thankyou's
-- thankyoutemplate table (definition.sql)
-- add template to admin stuff
-- seed the templates for prev years, right or wrong (seed.sql)

insert into instructions set
        table_name= 'thank_you_templates',
            action= 'Edit',
       instruction= "<p>Note that the main body of the letter includes these weird bits of text. They are very necessary. Here is what they represent and what gets substituted into them:</p><p><br />[:DATE:]  = The date the letter is printed</p><p>[:DEAR:] =  The donor's name, formatted as &quot;Ms. Somebody&quot;</p><p>[:NAME:] =  The donor's name, formatted as &quot;Ms. Firstname Lastname&quot;</p><p>[:ADDRESS:] = The complete formatted address of the donor</p><p>[:ITEMS:] = The items donated, formatted using other fields</p><p>[:VALUERECEIVED:] =  What the donor received. We must declare this for tax purposes.</p><p>[:FROM:] = The Solicitation salesperson who collected the donation </p><p>[:ITERATION:] = The number of years this Springfest is</p><p>[:ORDINAL:] = The nd/rd/th which goes with the number</p><p>[:YEAR:] =  The year of this Springfest</p><p>[:YEARS:] = The number of years the School has been in operation</p><p>[:EMAIL:] = The donor's email address (not really used right now)</p>",
_cache_instruction = 'Note that the main body of the letter includes these weird bits of text. They are very necessary. Here is what they represent and what gets substituted into them:[:DATE:]  = The date the letter is pri'
;

insert into instructions set
        table_name= 'thank_you_templates',
            action= 'Add',
       instruction= "<p>Note that the main body of the letter includes these weird bits of text. They are very necessary. Here is what they represent and what gets substituted into them:</p><p><br />[:DATE:]  = The date the letter is printed</p><p>[:DEAR:] =  The donor's name, formatted as &quot;Ms. Somebody&quot;</p><p>[:NAME:] =  The donor's name, formatted as &quot;Ms. Firstname Lastname&quot;</p><p>[:ADDRESS:] = The complete formatted address of the donor</p><p>[:ITEMS:] = The items donated, formatted using other fields</p><p>[:VALUERECEIVED:] =  What the donor received. We must declare this for tax purposes.</p><p>[:FROM:] = The Solicitation salesperson who collected the donation </p><p>[:ITERATION:] = The number of years this Springfest is</p><p>[:ORDINAL:] = The nd/rd/th which goes with the number</p><p>[:YEAR:] =  The year of this Springfest</p><p>[:YEARS:] = The number of years the School has been in operation</p><p>[:EMAIL:] = The donor's email address (not really used right now)</p><p>Also the logo currently doesn't appear here in this template, it's dropped into the PDF at print time.</p>",
_cache_instruction = 'Note that the main body of the letter includes these weird bits of text. They are very necessary. Here is what they represent and what gets substituted into them:[:DATE:]  = The date the letter is pri'
;
