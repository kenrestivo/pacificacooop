
#to log in
uo=ClientCookie.urlopen('http://www/coop-dev?auth[uid]=8&auth[pwd]=tester')

#the proxy
sp=jsonrpc.ServiceProxy('http://www/coop-dev/dispatchproxy.php')

sp2.echotest('foo')

sp2.throwPEARError('foo')

sp2.getPage('foo')
