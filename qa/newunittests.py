

#$Id$

htmlunitdir = '/usr/scratch/htmlunit-1.7/lib/'

import os
for i in os.listdir(htmlunitdir):
    sys.path.append('/'.join([htmlunitdir, i]))

