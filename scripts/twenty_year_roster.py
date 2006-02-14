#!/usr/bin/env python


"""
{'Address': '2120 Francisco',
 'Birthday': '28962',
 'Board Position': '',
 'Child(ren)': 'Shira',
 'Dad Name': 'Bob Zuckerman',
 'Last Name': 'Zuckerman',
 'Mom Name': 'Desda Zuckerman',
 'Phone': '355-5333',
 'Year Attended': '82-84'}
"""


from csvsqlobject import *
from datetime import date




#MOVE TO A LIBRARY!
def dateFix(d):
    """Deal with Excel dates, which sometimes come up unformatted
    as integers. NOTE the -2 to deal with excel/lotus bugs."""
    if d.count('/') > 1:
        return d
    else:
        return date.fromordinal(
            int(d) - 2 +
            date.toordinal(date(1900,1,1))).strftime("%m/%d/%y")



def un_y2k(y):
    """changes a yy to yyyy. returns an int. YOU deal with coercing it"""
    y= int(y)
    if y < 1900:
        y += 1900
    if y < 1950:
        y += 100
    return y


def fix_years(yy):
    """changes yy-yy string to yyyy-yyyy string"""
    return '-'.join([str(un_y2k(year)) for year in yy.split('-')])
            


def fix_board_position(pos):
    fixes={'1st VP': '1st Vice President',
           '2nd VP': '2nd Vice President',
           'Chair of Bd': 'President',
           'First VP': '1st Vice President'}
    try:
        return fixes[pos]
    except KeyError:
        return pos


def parse_board_positions_field(b):
    """truly ugly. change 'Position(yy-yy),Position(yy-yy)' to a struct"""
    return [[m.replace(')', '') for m in k.strip().split('(')] for k in b.split(',')]





def first_pass(data):
    """the first batch of data fixes. skip the first line, which is the header"""
    for i in range(0,len(data)):
        di=data[i]
        if di['Birthday'] != '':
            di['fixed_birthday'] = dateFix(di['Birthday'])
        if di['Board Position'] != '':
            di['boards'] = parse_board_positions_field(di['Board Position'])
        if di['Year Attended'] != '':
            di['all_attended'] = [fix_years(y) for y in di['Year Attended'].split(',')]
            di['fixed_attended'] = di['all_attended'].pop(0)
            
                




def fix_attended(data):
    """enrollment is in an ugly format: mixed lines on one. fix this."""
    for i in range(0,len(data)):
        di=data[i]
        if di.keys().count('fixed_attended') < 1:
            if data[i-1].keys().count('all_attended') and len(data[i-1]['all_attended']) > 0:
                di['fixed_attended'] = data[i-1]['all_attended'].pop(0)
            else:
                di['fixed_attended'] = data[i-1]['fixed_attended']
                
                


def make_enrollment_array(data):
    for i in range(0,len(data)):
        di=data[i]
        years=di['fixed_attended'].split('-')
        di['enroll'] = ['-'.join([str(i), str(i+1)]) for i in range(int(years[0]), int(years[1]))]
            



############# MAIN ##################
if __name__ = '__main__':
    rasta=[i for i in import_to_dict('/mnt/kens/ki/proj/coop/imports/AlumniRoster.csv') if i['Birthday'] != '']
    first_pass(rasta)
    fix_attended(rasta)




"""
########
[[i['Last Name'], i['Child(ren)'], i['fixed_attended']] for i in rasta]

###########  attended tests
enrol=[i['Year Attended'] for i in rasta if i['Year Attended'] != '']enrol
[[fix_years(y) for y in e.split(',')] for e in enrol]


####################
#board pos tests
bp=[i['Board Position'] for i in rasta if i['Board Position'] != '']
[parse_board_positions_field(j)  for j in bp]


######## birthday tests
[[dateFix(i['Birthday']), i['Last Name'], i['Child(ren)']] for i in rasta if i['Birthday'] != '']
"""
