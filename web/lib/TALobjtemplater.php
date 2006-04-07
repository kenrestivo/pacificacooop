<?php

/*
  Copyright © 2004-2006  ken restivo <ken@restivo.org>
 
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
 
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details. 
 
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

$Id$

*/


class ObjLocator
{
    var $path;
    var $page; // cache of coop page
    
    function ObjLocator($path, &$page)
        {
            $this->path = $path;
            $this->page =& $page;
        }

    
    function realPath()
    {
        return $this->path;
    }

    function lastModified()
    {
        // XXX ALWAYS regenerate this thing. in the future, grab the mod date
        return time();
    }

    function data()
    {
        ///XXX HACK FOR THANKYOU NOTES!
        return '<div metal:define-macro="main_body">'. 
            $this->page->thank_you_notes->obj->main_body .
            '</div>';

        // basically, everything after the obj://
        // XXX this is stupid, PHPTAL has its own way of resolving paths
        return $this->page->template->{str_replace('/', '->', 
                                                   substr($this->path, 6))};
  
    }
}





class ObjResolver extends PHPTAL_SourceResolver
{
    var $page; // cache of cooppage. annoying!

    function ObjResolver(&$page)
        {
            $this->page =& $page;
            // no constructor! PHPTAL_SourceResolver::PHPTAL_SourceResolver();
        }
    

    function resolve($path, $repository=false, $callerPath=false)
    {
        $this->path = $path; // this seems like the right place, no?

        // DBResolver can resolver only db:// based pathes
        if (!substr($path, 0, 6) == 'obj://') {
            return false;
        }

        //confessObj($this, 'the resolver');

        $locator = new ObjLocator($path, &$this->page);
        //if ($locator->isValid()) {
            return $locator;
            //}
        return false;
    }
}





?>