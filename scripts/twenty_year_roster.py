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


from sys import path
path.append('/mnt/kens/ki/is/python/lib')
path.append('/mnt/kens/ki/proj/coop/scripts')
path.append('/mnt/kens/ki/proj/coop/web')
path.append('/mnt/kens/ki/proj/coop/web/objects')


from csvsqlobject import *
from datetime import date,datetime




#MOVE TO A LIBRARY!
def dateFix(d):
    """Deal with Excel dates, which sometimes come up unformatted
    as integers. NOTE the -2 to deal with excel/lotus bugs.
    returns an SQL-formatted date... maybe"""
    if d.count('/') > 1:
        return _human_to_dt(d)
    else:
        return date.fromordinal(
            int(d) - 2 +
            date.toordinal(date(1900,1,1))).strftime("%Y-%m-%d")

    
def _human_to_dt(dob):
    """move to a library!!!"""
    d=map(int, dob.split('/'))
    d.insert(0,d.pop())             # date wants y,m,d
    if d[0] < 1900: d[0]+=1900
    if d[0] < 1950: d[0]+=100
    return datetime.date(*d)



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
    p=pos.strip()
    try:
        return fixes[p]
    except KeyError:
        return p


def parse_board_positions_field(b):
    """truly ugly. change 'Position(yy-yy),Position(yy-yy)' to a struct"""
    jobs=b.split(',')
    jobyears=[map(lambda x: x.replace(')', ''), i.split('(')) for i in jobs]
    cleanyears=[[fix_board_position(j[0]), fix_years(j[1])] for j in jobyears]
    return [dict(zip(['job_description', 'school_year'], jy)) for jy in cleanyears]




########### my "iterators" TODO: use real iterators. find out how.


def first_pass(data):
    """the first batch of data fixes. skip the first line, which is the header"""
    for i in range(0,len(data)):
        di=data[i]
        if di['Birthday'] != '':
            di['kids'] = {}
            di['kids']['date_of_birth'] = dateFix(di['Birthday'])
        if di['Board Position'] != '':
            di['job_assignments'] = parse_board_positions_field(di['Board Position'])
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
                
                


def make_enrollment(data):
    for i in range(0,len(data)):
        di=data[i]
        years=di['fixed_attended'].split('-')
        di['enrollment'] = ['-'.join([str(i), str(i+1)]) for i in range(int(years[0]), int(years[1]))]
            

 
def split_parents(data):
    """ this is ugly, and, i suspect, stupid too"""
    for i in range(0,len(data)):
        di=data[i]
        if di['Dad Name'] != '':
            di['dad'] = {}
            dad=di['Dad Name'].split(' ')
            di['dad']['first_name'] = dad[0]
            if len(dad) > 1:
                di['dad']['last_name'] = dad[1]
            else:
                di['dad']['last_name'] = di['Last Name']
        if di['Mom Name'] != '':
            di['mom'] = {}
            mom=di['Mom Name'].split(' ')
            di['mom']['first_name'] = mom[0]
            if len(mom) > 1:
                di['mom']['last_name'] = mom[1]
            else:
                di['mom']['last_name'] = di['Last Name']


def marshal_to_db(data):
    """might as well marshal all of them into a nested struct"""
    lnc = ""
    for i in range(0,len(data)):
        di=data[i]
        if di['Child(ren)']:
            di['kids']['first_name'] = di['Child(ren)']
            if di['Last Name']:
                di['kids']['last_name'] = di['Last Name']
                lnc = di['Last Name']
            else:
                di['kids']['last_name'] = lnc
        if di['Address']:
            di['families'] ={}
            di['families']['address'] = di['Address']
            di['families']['phone'] = di['Phone']
            di['families']['name'] = di['Last Name']
            
        


############# MAIN ##################
if __name__ == '__main__':
    """filter out lines with no birthday, which are invalid xrefs"""
    rasta=[i for i in import_to_dict('/mnt/kens/ki/proj/coop/imports/AlumniRoster.csv') if i['Birthday'] != '']
    first_pass(rasta)
    fix_attended(rasta)
    make_enrollment(rasta)
    split_parents(rasta)
    marshal_to_db(rasta)


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
