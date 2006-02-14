#!/usr/bin/env python


from csvsqlobject import *
from datetime import date


d=import_to_dict('/mnt/kens/ki/proj/coop/imports/AlumniRoster.csv')


#MOVE TO A LIBRARY!
def dateFix(self, d):
    """Deal with Excel dates, which sometimes come up unformatted
    as integers. NOTE the -2 to deal with excel/lotus bugs."""
    if d.count('/') > 1:
        return d
    else:
        return date.fromordinal(
            int(d) - 2 +
            date.toordinal(date(1900,1,1))).strftime("%m/%d/%y")



def un_y2k(y):
    """changes a yy to yyyy"""
    y= int(y)
    if y < 1900:
        y += 1900
    if y < 1950:
        y += 100
    return y


def fix_years(yy):
    """changes yy-yy to yyyy-yyyy"""
    return '-'.join([str(un_y2k(year)) for year in yy.split('-')])
            


def parse_board_positions(b):
    """truly ugly. change 'Position(yy-yy),Position(yy-yy)' to a struct"""
    return [[m.replace(')', '') for m in k.strip().split('(')] for k in b.split(',')]


#board pos
bp=[i['Board Position'] for i in d if i['Board Position'] != '']
[parse_board_positions(j)  for j in bp]

