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
import cgitb
cgitb.enable()
from sys import stdout
import session
import logging

class Page:
    """a minimal (so far) reimplementation of the php CoopPage class
    handles page display and header management"""
    session = None
    cpVars = {}
    debug = []
    raw_output = []
    headers = {}
    forminput = dict()
    template_name = ''
    auth = {}
    elements = dict(doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
                    title = 'Data Entry',
                    heading = 'Pacifica Co-Op Nursery School'
                     )

    def __init__(self):
        """hack around bug in cgi input"""
        self.forminput = dict([(i.name, i.value)
                               for i in cgi.FieldStorage(keep_blank_values=True).list])
        self.session=session.Session(self)


    def add_header(self, line):
        self.headers.append(line)


    def add_output(self, line):
        self.output.append(line)


    def add_debug(self, line):
        self.debug.append(line)


    def add_line(self, line, type):
        """so that programs can decide at runtime"""
        getattr(self, type).append(line)


    def logIn(self):
        try:
            self.auth = self.session.data['auth']
            self.cpVars = self.session.data['cpVars']
        except KeyError:
            return "TODO: port the log in code here!"


    def render_raw(self, debug = False):
        """outputs the page, headers first
        XXX this may or may not survive being simpletal'ed"""
        for i in  self.headers:
            print '%s: %s' % (i, self.headers[i].strip())
        print
        if debug:
            for i in self.debug:
                print i
        for i in self.raw_output:
            print i


    def output_headers(self):
        for i in  self.headers:
            print '%s: %s' % (i, self.headers[i].strip())
        print


    def render_template(self):
        from simpletal import simpleTAL, simpleTALES
        self.output_headers()
        logging.getLogger('simpleTAL').setLevel(logging.INFO)
        logging.getLogger('simpleTALES').setLevel(logging.INFO)
        context = simpleTALES.Context()
        context.addGlobal('page', self)
        templateFile = open ('templates/%s.xhtml' % (self.template_name), 'r')
        template = simpleTAL.compileXMLTemplate (templateFile)
        templateFile.close()
        template.expand (context, stdout, docType=self.elements['doctype'],
                         suppressXMLDeclaration=True)


