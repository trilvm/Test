<?php

/**
 * ErrorController - The default error controller class
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';
require_once ('auth/models/LogModel.php');
class ErrorController extends Zend_Controller_Action
{

    /**
     * This action handles  
     *    - Application errors
     *    - Errors in the controller chain arising from missing 
     *      controller classes and/or action methods
     */
    public function errorAction()
    {
        
    	$this->view->title = "Lỗi Hệ Thống";
    	
    	if($this->_request->isGet()){
    		$id = explode(",",$this->_request->getParam('id'));
    		$mod = $this->_request->getParam('mod');
    		$controller = $this->_request->getParam('control');
    		$action = $this->_request->getParam('action');
    		if($id){
    			require_once("config/".$mod.".php");
    			foreach ($id as $e){
    				if($e){
    					eval('$message = '.$e.';');
    					$this->view->message .= $message.', ';
    				}
    			}
    			$this->view->backurl = '/'.$mod.'/'.$controller;
    			$this->view->subtitle.=$mod."_".$controller;
    		}
    		else {
    			$errors = $this->_getParam('error_handler');
        		switch ($errors->type) {
           	 		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            		case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                	// 404 error -- controller or action not found                
               	 	$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
                	$this->view->title = 'HTTP/1.1 404 Not Found';
                	break;
            		default:
                	// application error; display error page, but don't change                
                	// status code
                	$this->view->title = 'Application Error';
               		 break;
        		}
        		$this->view->message = $errors->exception;
        	}
        	LogModel::Logging($mod,$controller,$action,$this->_request->getParam('id'));
        	
    	}
    	
    }
    	
    	
    
}
