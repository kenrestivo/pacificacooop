#!/usr/bin/env python


from PHPUnserialize import *


sessionstring = 'foo|a:2:{s:3:"bar";s:4:"baz|";s:4:"blah";a:1:{s:4:"now|";s:4:"what";}}ugh|s:5:"aaahg";'

regularstring = 'a:2:{s:3:"foo";a:2:{s:3:"bar";s:4:"baz|";s:4:"blah";a:1:{s:4:"now|";s:4:"what";}}s:3:"ugh";s:5:"aaahg";}'

u = PHPUnserialize()

print u.unserialize(regularstring)
print u.unserialize(sessionstring)
