"""
a simple library for session management that interoperates with the
PHP $_SESSION stuff, as implemented in the coop database
i.e. the SessionInfo or session_info table in mysql
it's not very extensible at this time, beyond the coop database
"""


import model
import Cookie
from phpserialize.PHPSerialize import PHPSerialize
from phpserialize.PHPUnserialize import PHPUnserialize
from posix import environ


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

    def write_session(self):
        pass


    def __init__(self, page):
        try:
            self.get_session()
        except NoSessionSaved:
            self.create_session()
            

    def get_session(self):
        if not environ.has_key('HTTP_COOKIE'):
            raise NoSessionSaved
        self.recv_cookies=Cookie.BaseCookie(environ['HTTP_COOKIE'])
        if not self.recv_cookies.has_key(self.key_name)):
            raise NoSessionSaved
        self.recv_cookie_dict=dict(
            [(i[0],i[1].value) for i in self.recv_cookies.items()])
        self.page.debug.append('found cookies: %s' % (
            self.recv_cookie_dict))
        self.db_obj = model.SessionInfo.get(
            self.recv_cookie_dict[self.key_name])
        self.session_data = phpun.session_decode(self.db_obj.vars)
                
           
    def create_session(self):
        new_cookies=Cookie.BaseCookie()
        newid = 'XXXtestnewsession'
        self.new_cookies[self.key_name] = newid
        self.page.headers.append(repr(self.new_cookies))
        self.page.debug.append('new cookies: "%s<br />"' % (str(self.new_cookies)))
    

