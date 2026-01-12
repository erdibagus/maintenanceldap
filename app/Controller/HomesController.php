<?php
class HomesController extends AppController{
	public $components = array('Function');
	
	function index(){
		if ($this->request->header('HX-Request')) {
			$this->layout = false;
		}
	}
}
	
?>



