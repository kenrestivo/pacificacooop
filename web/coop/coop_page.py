# 	Copyright (C) 2006  ken restivo <ken@restivo.org>
	 
# 	This program is free software; you can redistribute it and/or modify
# 	it under the terms of the GNU General Public License as published by
# 	the Free Software Foundation; either version 2 of the License, or
# 	(at your option) any later version.
	
# 	 This program is distributed in the hope that it will be useful,
# 	 but WITHOUT ANY WARRANTY; without even the implied warranty of
# 	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# 	 GNU General Public License for more details. 
	
# 	 You should have received a copy of the GNU General Public License
# 	 along with this program; if not, write to the Free Software
# 	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

import cgi

class Page:
    """a minimal (so far) reimplementation of the php CoopPage class
    handles page display and header management"""
    debug = []
    output = []
    headers = {}
    forminput = dict()

    def __init__(self):
        """hack around bug in cgi input"""
        self.forminput = dict([(i.name, i.value)
                               for i in cgi.FieldStorage(keep_blank_values=True).list])

    def add_header(self, line):
        self.headers.append(line)


    def add_output(self, line):
        self.output.append(line)


    def add_debug(self, line):
        self.debug.append(line)


    def add_line(self, line, type):
        """so that programs can decide at runtime"""
        getattr(self, type).append(line)

    def render(self, debug = False):
        """outputs the page, headers first
        XXX this may or may not survive being simpletal'ed"""
        for i in  self.headers:
            print '%s: %s' % (i, self.headers[i].strip())
        print
        if debug:
            for i in self.debug:
                print i
        for i in self.output:
            print i
