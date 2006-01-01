<?php

class XML_to_HTML extends PHPTAL_Filter
{
    function filter(&$tpl, $data, $mode)
    {
         // make it valid html, not xml
        $patterns = array('/(<\?xml.*?>)/sm' => '',
                          '/(<[meta|link].*?)\/>/sm' => '$1 >',
                          '/.*?<\!DOCTYPE/sm' => '<!DOCTYPE');

        return preg_replace(array_keys($patterns), 
                            array_values($patterns), 
                            $data);
    }
}

?>