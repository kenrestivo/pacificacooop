

#$Id$

htmlunitdir = '/usr/scratch/htmlunit-1.7/lib/'

import os,sys
for i in os.listdir(htmlunitdir):
    sys.path.append('/'.join([htmlunitdir, i]))


import com.gargoylesoftware.htmlunit 


## batteries NOT INCLUDED!
sys.path.append('/usr/scratch/commons-httpclient-3.0-rc4/commons-httpclient-3.0-rc4.jar')


wc = com.gargoylesoftware.htmlunit.WebClient()
