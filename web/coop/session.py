
import model
import Cookie
from phpserialize.PHPSerialize import PHPSerialize
from phpserialize.PHPUnserialize import PHPUnserialize
from posix import environ


class Session:
    phpun=PHPUnserialize()
    phpize=PHPSerialize()
    recv_cookies= None
    recv_cookie_dict = {}
    new_cookies = None
    page=None
    session_data = {}

if environ.has_key('HTTP_COOKIE'):
    self.recv_cookies=Cookie.BaseCookie(environ['HTTP_COOKIE'])
    self.recv_cookie_dict=dict([(i[0],i[1].value) for i in self.recv_cookies.items()])
    page.debug.append('found cookies: %s' % (self.recv_cookie_dict))
    self.session_data = phpun.session_decode(model.SessionInfo.get(self.recv_cookie_dict['coop']).vars)
    #TODO: cache the db object too-- will need to save the session later

if not (environ.has_key('HTTP_COOKIE') and self.recv_cookies.has_key('coop')):
    new_cookies=Cookie.BaseCookie()
    newid = 'XXXtestnewsession'
    self.new_cookies['coop'] = newid
    #TODO: save the  new session!
    page.headers.append(repr(self.new_cookies))
    page.debug.append('new cookies: "%s<br />"' % (str(self.new_cookies)))


