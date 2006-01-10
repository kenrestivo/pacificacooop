#$Id$

import cgi
import cgitb
cgitb.enable()


##TODO: check dev!
host='bc'
db='coop'
user='input'
pw='test'
port=3306

connectionurl=     'mysql://%s:%s@%s:%d/%s' % (user, pw, host, port, db)

# need this everywhere
#Rsitepackages = '/mnt/kens/ki/proj/coop/web/site-packages'


