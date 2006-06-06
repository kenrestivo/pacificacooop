//this module is currently in beta release.

Eventrepeat enables the creation of repeating events for node types that are event-enabled. 

Eventrepeat's pattern creation was largely modeled on the iCal RRULE specification.  
At this time, it should support all RRULE parameters, with the following exceptions:

   1. Recurrance periods less than DAILY
   2. BYDAY declarations greater than 5 and less than -5 (ex. 20th Monday of the year 
      is not supported).  Other similar patterns can be built that should approximate 
      this functionality.
   3. BYSETPOS parameter

For installation instructions, see INSTALL.txt in this folder

For more information on how to create repeat sequences, visit adiminister->help->eventrepeat
after module installation.

The following is a wish list for ugrades to this module.  If you'd be interested in
providing financial sponsorship for any of these features, please email
thehunmonkgroup@yahoo.com

//  TODO's
//  2. add ical support