"""
a simple library for session management that interoperates with the
PHP $_SESSION stuff, as implemented in my application database
i.e. the SessionInfo or session_info table in mysql
it's not very extensible at this time, beyond the coop database
"""


import model
import Cookie
from phpserialize.PHPSerialize import PHPSerialize
from phpserialize.PHPUnserialize import PHPUnserialize
from posix import environ
import random
import md5

class NoSessionSaved(Exception):
    pass


class Session:
    phpun=PHPUnserialize()
    phpize=PHPSerialize()
    recv_cookies= None
    recv_cookie_dict = {}
    new_cookies = None
    page=None
    session_data = {}
    db_obj = None
    key_name = 'coop'
    remote_ip = '0.0.0.0'

    def write_session(self):
        pass


    def __init__(self, page):
        self.page = page
        self.remote_ip = self.guess_ipaddr()
        try:
            self.get_session()
        except NoSessionSaved:
            self.create_session()
            

    def get_session(self):
        if not environ.has_key('HTTP_COOKIE'):
            raise NoSessionSaved
        self.recv_cookies=Cookie.BaseCookie(environ['HTTP_COOKIE'])
        if not self.recv_cookies.has_key(self.key_name):
            raise NoSessionSaved
        self.recv_cookie_dict=dict(
            [(i[0],i[1].value) for i in self.recv_cookies.items()])
        self.page.debug.append('found cookies: %s' % (
            self.recv_cookie_dict))
        self.db_obj = model.SessionInfo.get(
            self.recv_cookie_dict[self.key_name])
        self.session_data = phpun.session_decode(self.db_obj.vars)
        self.db_obj.ip_addr = self.remote_ip
                
           
    def create_session(self):
        self.new_cookies=Cookie.BaseCookie()
        self.new_cookies[self.key_name] = self.generate_key()
        self.page.headers.append(repr(self.new_cookies))
        self.page.debug.append('new cookies: "%s<br />"' % (
            str(self.new_cookies)))
    

    def generate_key(self):
        """this algorithm is from ilovejackdaniels.com. i'm not kidding"""
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
