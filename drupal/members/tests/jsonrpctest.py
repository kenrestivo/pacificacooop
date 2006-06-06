import ClientCookie

from sys import path
path.append('/mnt/www/restivo/py/jsonrpc')

import jsonrpc

#to log in
uo=ClientCookie.urlopen('http://www/coop-dev?auth[uid]=8&auth[pwd]=tester')

#the proxy
sp=jsonrpc.ServiceProxy('http://www/coop-dev/services/api_server.php')

sp.ping('foo')


sp.getPage()
