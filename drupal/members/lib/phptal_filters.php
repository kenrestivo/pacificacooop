<?php

/*
	Copyright (C) 2004-2005  ken restivo <ken@restivo.org>
	 
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
*/

/// GENERIC CONVERSION
class XML_to_HTML extends PHPTAL_Filter
{
    function filter(&$tpl, $data, $mode)
    {
         // make it valid html, not xml
        $patterns = array('/(<\?xml.*?>)/sm' => '', // xml declaration
                          '/(<.*?)\/>/sm' => '$1 >', // self-closing /> tags
                          '/.*?<\!DOCTYPE/sm' => '<!DOCTYPE'); // leading space

        return preg_replace(array_keys($patterns), 
                            array_values($patterns), 
                            $data);
    }
}

/// FOR PDML ONLY!!!
class XHTML_to_PDML extends PHPTAL_Filter
{
    function filter(&$tpl, $data, $mode)
    {
         // make it valid html, not xml
        $patterns = array(
            // XXX pdml doesn't support div's inside of cells
            '/<div.*?>(.*?)<.*?>/sm' => '$1<br>',
            // XXX tiny_mce inserts spaces that cause pdml to freak out
            '/\s*(<br.*?>)\s*/sm' => '$1' ,
            '/<\/p>/' => '<br></p>' // PDML doesn't linefeed after a P
            // this one doesn't really work: '/>\s*(.*?)\s*</sm' => '/>$1<'
            ); 


        return preg_replace(array_keys($patterns), 
                            array_values($patterns), 
                            $data);
    }
}


?>