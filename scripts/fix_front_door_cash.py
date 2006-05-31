#!/usr/bin/env python


from sys import path
path.append('/mnt/kens/ki/is/python/lib')
path.append('/mnt/kens/ki/proj/coop/scripts')
path.append('/mnt/kens/ki/proj/coop/web/coop')
path.append('/mnt/kens/ki/proj/coop/web/objects')


from model import *


## one-off script to manually fix all the people who bought tix with cash

ticket_price = 30

oldinc =Income.get(811)

tickets = Tickets.select(Tickets.q.incomeID == 811)
                         
total = 0

for t in tickets:
    print "creating cash income for ticket %d, %d tickets" %(t.ticketID,
                                                             t.ticketQuantity)
    inc = Income(checkNumber =oldinc.checkNumber, checkDate=oldinc.checkDate,
                 payer=oldinc.payer, accountNumber=oldinc.accountNumber,
                 schoolYear = oldinc.schoolYear,
                 paymentAmount = t.ticketQuantity * ticket_price)
    t.incomeID = inc.incomeID
    total = total + t.ticketQuantity * ticket_price


extra = oldinc.paymentAmount - total

print "$%0.02f original amount, $%0.02f new tickets created" %(oldinc.paymentAmount, total)

if extra != 0:
    print "old income - total new tickets =  $%0.02f still unaccounted for!" % (extra)

if extra > 0:
    inc = Income(checkNumber =oldinc.checkNumber, checkDate=oldinc.checkDate,
                 payer=oldinc.payer, accountNumber=oldinc.accountNumber,
                 schoolYear = oldinc.schoolYear,
                 paymentAmount = t.ticketQuantity * ticket_price)


oldinc.delete(oldinc.incomeID)
