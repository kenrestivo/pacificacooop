#!/usr/bin/env python


from csvsqlobject import *

d=import_to_dict('/mnt/kens/ki/proj/coop/imports/AlumniRoster.csv')


def parse_board_positions(b):
    return [k.strip() for k in b.split(',')]

#board pos
bp=[i['Board Position'] for i in d if i['Board Position'] != '']
[parse_board_positions(j)  for j in bp]
