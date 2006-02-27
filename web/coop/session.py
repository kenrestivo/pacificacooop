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

__version__ = """$Id$"""



"""
a simple library for session management that interoperates with the
PHP $_SESSION stuff, as implemented in my application database
i.e. the SessionInfo or session_info table in mysql
it's not very extensible at this time, beyond the coop database
"""


from objects import model
import Cookie
from phpserialize.PHPSerialize import PHPSerialize
from phpserialize.PHPUnserialize import PHPUnserialize
from posix import environ
import random
import md5
import re
from datetime import datetime

class NoSessionSaved(Exception):
    pass


class Session:
    """session-management operations.
    sid is only set when a new key is created,
    or when the browser doesn't send a cookie
    """
    phpun=PHPUnserialize()
    phpize=PHPSerialize()
    recv_cookies= None
    recv_cookie_dict = {}
    new_cookies = None
    page=None
    data = {}
    db_obj = None
    sid = ''
    key_name = 'coop'
    remote_ip = '0.0.0.0'

    def write_session(self):
        self.db_obj.vars = self.phpize.session_encode(self.data)


    def __init__(self, page):
        self.page = page
        self.remote_ip = self.guess_ipaddr()
        try:
            self.get_session()
        except NoSessionSaved:
            self.create_session()
            

    def get_session(self):
        if environ.has_key('HTTP_COOKIE'):
            self.recv_cookies=Cookie.SimpleCookie(environ['HTTP_COOKIE'])
            if not self.recv_cookies.has_key(self.key_name):
                raise NoSessionSaved
        elif self.page.forminput.has_key(self.key_name):
            self.recv_cookies=Cookie.SimpleCookie('%s=%s' % (
                self.key_name, self.page.forminput[self.key_name]))
        else:
            raise NoSessionSaved
        self.recv_cookie_dict=dict(
            [(i[0],i[1].value) for i in self.recv_cookies.items()])
        self.page.debug.append('found cookies: %s' % (
            self.recv_cookie_dict))
        self.db_obj = model.SessionInfo.get(
            self.recv_cookie_dict[self.key_name])
        self.data = self.phpun.session_decode(self.db_obj.vars)
        self.db_obj.ip_addr = self.remote_ip
        self.page.debug.append(self.data)
                
           
    def create_session(self):
        """XXX note the hack here. userid and vars need to be something
        but that will require me to get my auth shit ported over too"""
        self.new_cookies=Cookie.SimpleCookie()
        self.sid = self.generate_key()
        self.new_cookies[self.key_name] = self.sid
        self.new_cookies[self.key_name]['path'] = '/'
        now = datetime.now()
        model.SessionInfo(
            session_id=self.sid,
            ip_addr=self.remote_ip, entered=now, updated=now,
            UserID=0, vars={})
        #NOTE! you must now go GET it because above doesn't return right
        self.db_obj=model.SessionInfo.get(self.new_cookies[self.key_name].value)
        self.page.headers['Set-Cookie'] = self.new_cookies.output(header='')
        self.page.debug.append('new cookies: "%s<br />"' % (
            str(self.new_cookies)))
    

    def generate_key(self):
        """this algorithm is from ilovejackdaniels.com. i'm not kidding
        i like that it's so terse. how readable it is, remains to be seen """
        random.seed()
        rnd=random.randint(1,9)
        return '%s%s%s' % (rnd,
                           md5.new(self.remote_ip).hexdigest()[0:11+rnd],
                           md5.new(str(random.randint(1,1000000))).hexdigest()[random.randint(1,32-rnd):21-rnd])


    def guess_ipaddr(self):
        """alas, so many ways to guess an IP address"""
        if environ.has_key('HTTP_X_FORWARDED_FOR'):
            return re.subn(',.*', '', environ['HTTP_X_FORWARDED_FOR'])[0]
        if environ.has_key('HTTP_CLIENT_IP'):
            return environ['HTTP_CLIENT_IP']
        if environ.has_key('REMOTE_ADDR'):
            return environ['REMOTE_ADDR']
        return 'Unknown'

