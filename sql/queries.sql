-- $Id$
-- various narsty join queries that are so hairy, i want to keep track of them

-- Copyright (C) 2003  ken restivo <ken@restivo.org>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details. 
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

select last, first 
	from kids 
		left join keglue on kids.kidsid = keglue.kidsid 
		left join enrol on enrol.enrolid = keglue.enrolid 
	where enrol.sess = "PM";

-- contact info for all parents, showing who is and isn't a worker
select families.name, parents.last, parents.first , parents.worker, families.phone, parents.email 
	from parents 
		left join families on parents.familyid = families.familyid 
	order by families.name, parents.last, parents.first;

-- expired licenses
select parents.last, parents.first, lic.expires 
	from parents 
		left join lic on parents.parentsid = lic.parentsid 
	where parents.worker = "Yes" and (expires is null or expires < now())
	group by parents.last, parents.first;


-- contact info all families whose working parent doesn't have a vaild dl
select families.name, parents.last, parents.first, lic.expires, families.phone, parents.email
	from families 
		left join parents on parents.familyid = families.familyid 
		left join lic on parents.parentsid = lic.parentsid 
	where parents.worker = "Yes" and (expires is null or expires < now())
	group by parents.last, parents.first;


-- give me ocntact info for all families who have expired insurance
-- XXX: this MAY be the worker, it may not, i don't know
-- XXX this is THROUHOGHLY FUCKED UP! it ignores matches on the nonworker
select families.name, parents.last, parents.first, ins.expires,
		families.phone, parents.email
	from families 
		left join parents on parents.familyid = families.familyid 
		left join ins on parents.parentsid = ins.parentsid 
	where expires is null or expires < now()
	group by families.name;

--- EOF
