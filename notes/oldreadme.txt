
NOTE: these are the OLD readme notes from the deprecated, no-longer-used framework. no longer relevant

instead of using some off-the-shelf open-source php web application framework (which i SHOULD have done!) i wrote my own set of gui widgets and database abstractions. in retrospect this was an error but i'm stuck with it now. so, i'm documenting it so that anyone maintaining this (including myself) can figure out what's what.

general philosophy
	i'm a gtk/gnome linux guy. i hate html. so, i abstracted out all the html, and wrote a set of "widgets" to do things: draw popups, display fields, etc. this isn't new: postnuke and other such systems take a similar approach.


the fieldstruct
	this is the fundamental structure of the whole interface
	a fieldstruct is a VIEW of data.
		not analogous to a table, or even necessarily a privilge realm
	all its possible members and all possible values of each of its members!
		callback: the field's display isn't determined by it's type
			but rather a callback you supply is executed instead
			with the arguments: $i, $fname, $field
				where $i is the entrynumber (for spreadsheet-like editing)
				fname is the name of the field, and $field is its struct
					populated with any values the user input
		choices: force type to be a popup field
			the choices are an array supplied... these are displayed
			each choice can also be an array itself! 
				with 'set' and 'show' being the option value and the display
		def: the default value. this is the MOST important field!
			this is either the default value that the PROGRAMMER entered,
			or the value that teh user entered. yes overloads suck.
			has to be overloaded, so that entry and confirm forms use same code
		display: the TITLE of the field, which is displayed 
			in reports, and in entry forms
		dupeignore: don't check this field when calculating duplicates
			i.e. leave the value in this field out of any "select's"
			which means, if the fields are different in this field,
				they STILL are duplicates if the others match
			NOTE: NEVER EVER make an index field (i.e. unique id) a dupeignore
		duplicates: DEPRECIATED! not used.
		len: maximum length of this field, in chars. html enforces this.
		lines: if > 1, it's a SCROLLING text box, of this number of "rows"
		linkfield: don't display the data in this fieldname in this table. 
			instead, show the date in $linkfield, from $linktable
			the $fieldname data is saved/checked in $table, but not shown
			instead, $linkfield is shown.
		linktable: data in $linkfield comes from a join to THIS table
		required: user must enter SOME valid data for this field
		size: size of the entry box, in chars. html draws this
		table: table from which this field comes.
		type: type of field. used mostly for user entry validation, 
			and for display formatting. valid values are:
				date: a 2004-01-01 type date
				datetime: a date, but with a 24:00:00 time
				currency: in this case, US dollaz
				url: show the href for linking
		viewcallback: instead of showing the raw data, use this callback 
			called with ($fname, $val)
			$fname is the field name
			$val is the raw data value (which the callback then formats)
		perms: each field can have its own set of perms
			default is everyone can do everything
			the levels are array (group, user). 
		realval: with fieldstructs used for display, has the REAL value
			i.e. the value of the 'linkfield', not of the field itself
		savealways: a hack. if this flag exists, ignore the perms system :-/
			this is for index fields and such
		uniqueness: array of fields to search for unique-distinguising stuff
			in a pop-up. if the display field has dupes, 
				dig through this looking for uniqueness to display
		showall: if there's a uniqueness, show this number of columns ALWAYS
			it's smart enough to not let you show more columns than exist.
			so if uniqueness => array('col1, 'col2', 'col3')
			if you set showall=> 2, it'll ALWAYS show col1 and col2
				even if it's unique at col1 and col2+ aren't needed
		check_jointo: for popups. only show the entries where 
			the linktable is in the $callbacks['join'] somewhere
			NOTE! for this to work, there must be a join_to_table column
				in the sql table 'linktable', which the popuputll then checks
			 for cases where each individual entry has a different jointo
		joins: only relevant to pop-up.
			an array of arrays of joins, just like for $callbacks.
			use this if you need to do  hairy popup joins
		school_year: only relevant to a pop-up.
			array (which school year to constrain this pop-up to,
					which table to pull the schoolyear out of!)
		realname: if this $fieldname is a NASTY HACK AROUND MYSQL, then
				'realname' is the REAL name of the field
		constrainIndex: a nasty-ass hack. constrain the popup to match an index
				in this fieldstruct. i.e. to match some value in the maintable

callbacks struct
	this is a proto-object. it contains miles and miles of crap
		it'll eventually be an object, but i'm not ready to take that leap yet
		these are tied DIRECTLY to field structs, and will subsume them
	maintable: the, um, main table. the table that this view is mostly "about"
	mainindex: the unique id (index) field in the maintable
	realm: privilege realm for this view. 
	countindex: the field in $fields which should be where'd for counting
		this can be a linkfield or not.
	sumfield: sets the mode of the count to 'sum', using this field for totals
	description: long description for top menu
	shortdesc: short description for sidebar or popup treemenu
	allyears: don't schoolyearify (the default is to schoolyearify)
		that means, show all records for all years, not just this one
	fields: the NAME of the fieldspec array. eventually will be REFERENCE here
	joins: a recursive array of arrays of join info, 
		in order of joining (important!)
		each MUST start from the 'maintable'. period. no argument. it must.
		each containing :
			the table to join to
			the index that is common to the joins
				indexes MUST have the same name! no fancy index naming!
			optional recursive set of (table, index) arrays to join to THIS one
		 	so, to join maintable->atable, just:
				array ('atable', 'aindex')
			or, to join maintable->atable->btable->ctable,
				array ('atable, 'aindex',
					array('btable', 'bindex',
						array('ctable', 'cindex)))
				and, yes, i am becoming a LISP programmer. ;-)
	input callback: is DEPRECIATED. edit callback is now used instead
	dupecheck callback: used by processoneentry to dupecheck b4 saving
	realsave callback: used by processoneentry to REALLY save the thing!
	plus, all the state callbacks (see below)

		
passing variables around
	the rule is: ALWAYS convert to the relevant domain, 
		at the LAST possible minute
	the exception is for id's. i don't bother escaping id's
		they should all be numeric anyway... *should*.... 
	formatForSQL(): converts plain or HTML-format to conform to MYSQL rules
		called JUST before saving or querying.
	formatForHTML(): converts plain or SQL-format to display in proper HTML
		called JUST before displaying. checkLinks() calls it for you too.


the main state machine
	each web page is basically a state machine. html is stateless, and i hate that. so, i made it stateful by passing stuff through. it does NOT deal with cookies since many browsers have them turned off. and, since we avoid client-side javascript, it has to do lots of back-and-forth to get data validated, etc.
	here are the possible states, and the actions and TODO the args that their callbacks take:
			'confirmdelete': display the fields, read-only, with confirm box
			'delete': delete the fields.
			'edit' : prepopulate with data, and show it to the user for editing
			'save': check its validity, and dump it to database!
			'add': show the fields, populated with 'def' default values
			'view' : show a report, with the view data
			'summary': show just a top-level count/summary
			'replace': same as save, but with "replace into" not "insert into"
			'done' : no action, displaying is done.
	and the possibel field members too: TODO


------
other deprecated crap

fb_longHeader 
fb_addNewLinkFields = OBSOLETE. i use perms now to determine
fb_searchSelects = array() fields for searchselect. XXX HACK-- will use N instead
