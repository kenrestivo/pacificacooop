-- $Id$
-- various narsty join queries that are so hairy, i want to keep track of them

select last, first 
	from kids 
		left join keglue on kids.kidsid = keglue.kidsid 
		left join enrol on enrol.enrolid = keglue.enrolid 
	where enrol.sess = "PM";

-- contact info for all parents
select parents.last, parents.first , families.phone, parents.email 
	from parents 
		left join families on parents.familyid = families.familyid ;


-- expired licenses
select parents.last, parents.first, lic.expires 
	from lic 
		left join parents on parents.parentsid = lic.parentsid 
	where expires < now() group by parents.last, parents.first;


-- give me ocntact info for all parents who have expired licenses
select parents.last, parents.first, lic.expires,  families.phone, parents.email
	from lic 
		left join parents on parents.parentsid = lic.parentsid 
		left join families on parents.familyid = families.familyid 
		left join kids on kids.familyid = families.familyid 
	where expires < now() group by parents.last, parents.first;

-- give me ocntact info for all parents who have expired insurance
select parents.last, parents.first, ins.expires,  
	ins.companyname, ins.policynum, families.phone, parents.email
	from ins 
		left join parents on parents.parentsid = ins.parentsid 
		left join families on parents.familyid = families.familyid 
		left join kids on kids.familyid = families.familyid 
	where expires < now() group by parents.last, parents.first;
