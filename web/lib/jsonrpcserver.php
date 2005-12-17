<?php
// $Id$
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

significant chunks lifted from HTML_AJAX:

 * OO AJAX Implementation for PHP
 *
 * category   HTML
 * package    AJAX
 * author     Joshua Eichorn <josh@bluga.net>
 * copyright  2005 Joshua Eichorn
 * license    http://www.opensource.org/licenses/lgpl-license.php  LGPL
 * version    Release: 0.3.1

and from jsolait

  Copyright (c) 2004 Jan-Klaas Kollhof
  
  This file is part of the JavaScript o lait library(jsolait).


 */



require_once('PEAR.php');
require_once('Services/JSON.php');


class JSON_RPC_Server {

    var $exportedMethods = array();
    var $json;
    var $request; // json object of request
    var $response = array('id'=> NULL,
                          'result' => NULL,
                          'error' => NULL); // skeletal response
    var $content = 'text/plain';
    var $instance;

    function JSON_RPC_Server()
        {
            $this->json =& new Services_JSON();        
        }


    /* lifted from PEAR::HTML_AJAX */    
    function registerClass(&$instance, $exportedName = false, 
                           $exportedMethods = false) 
        {
            $this->instance =& $instance;
            
            if ($exportedMethods === false) {
                $this->exportedMethods = $this->_getMethodsToExport();
            }

        }

    /* lifted from PEAR::HTML_AJAX */    
    function _getMethodsToExport() 
        {
            $className = strtolower(get_class($this->instance));
            $funcs = get_class_methods($className);

            foreach ($funcs as $key => $func) {
                if (strtolower($func) === $className || substr($func,0,1) === '_') 
                {
                    unset($funcs[$key]);
                }
            }
            return $funcs;
        }




    /* lifted from PEAR::HTML_AJAX 
     */    
    function _sendResponse()
        {

            $output = $this->json->encode($this->response);
            
            // XXX what if it fails encoding, for some reason?


            // headers to force things not to be cached:
            $headers = array();
            //OPERA IS STUPID FIX
            if(isset($_SERVER['HTTP_X_CONTENT_TYPE']))
            {
                $headers['X-Content-Type'] = $this->content;
                $this->content = 'text/plain';
            }
            
            $headers['Content-Length'] = strlen($output);
            $headers['Expires'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
            $headers['Last-Modified'] = gmdate( "D, d M Y H:i:s" ) . 'GMT';
            $headers['Cache-Control'] = 'no-cache, must-revalidate';
            $headers['Pragma'] = 'no-cache';
            $headers['Content-Type'] = $this->content.'; charset=utf-8';
            
            
            $this->_sendHeaders($headers);
            
            user_error($output, E_USER_NOTICE);
            print $output;
        }


    /* lifted from PEAR::HTML_AJAX */    
    function _sendHeaders($array) 
        {
            foreach($array as $header => $value) {
                header($header .': '.$value);
            }
        }

    
    function _checkRequest()
        {
            // mochitest uses id = 0. don't bother checking it.
            if(empty($this->request->method) ||
               empty($this->request->params))
            {
                // error out
                $this->response['error'] = 'Invalid JSON-RPC request';
                return;
            }

            // make sure the method is in there too
            if(!in_array(strtolower($this->request->method), 
                         $this->exportedMethods)){
                $this->response['error'] = 
                    sprintf(
                        '%s is not a valid method for this class. Valid are: [%s]',
                        $this->request->method,
                        implode(', ', $this->exportedMethods));
                $this->response['result'] = $this->json->encode($this->request);
            }
        }
    

 
    function handleRequest()
        {
            $rawrequest = file_get_contents("php://input");
            //user_error($rawrequest, E_USER_NOTICE);

            $this->request = $this->json->decode($rawrequest);
            //XXX what to do if the json decode fails?

            $this->_checkRequest();

            // does this belong here? needs to happen somewhere
            $this->response['id'] = $this->request->id;



            ///TODO: surf through the entire response, looking for jsonclass
            ///if you find it, instantiate whatever it's supposed to be
            ///tricky in PHP. easy in python. scary in javascript.


            if($this->response['error']){
                // XXX:  call errorcallback instead
                $this->_sendResponse();
                return; // don't need to die, really
            }

            // put on the error condom
			PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 
								   array(&$this, '_PEARErrorHandler'));
            set_error_handler(array(&$this,'_errorHandler'));

            /// look at htmlajax, which i believe does it
            $this->response['result'] = call_user_func_array(
                array(&$this->instance, $this->request->method), 
                $this->request->params);

            restore_error_handler(); // remove error condom

            // if i get this far, i'm golden
            $this->_sendResponse();
            
        }

    /** lifted from htmlajax */
    function _PEARerrorHandler(&$obj)
        {
            restore_error_handler(); // otherwise it recurses!

            //SEGFAULTS! $this->response['error']['stack'] = $obj->backtrace;
            
            // XXX if there is a cooppage, do the old pear error here!
            $this->_errorHandler($obj->code, 
                                 $obj->message, 
                                 '', 
                                 '');
            
        }
     

    /** lifted directly from htmlajax */
    function _errorHandler($errno, $errstr, $errfile, $errline) 
        {
            if ($errno & error_reporting()) {
            
                $this->response['error'] = $errstr;


                $this->_sendResponse();
            
                // TODO: log it. how? restore error handler?
                exit(1);

            }
        }



} /// END JSONRPCSERVER CLASS



?>