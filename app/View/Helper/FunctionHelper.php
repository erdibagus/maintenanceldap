<?php
App::uses('AppHelper','View/Helper');
class FunctionHelper extends AppHelper{

public function cekJScript(){
	echo"<noscript><meta http-equiv='REFRESH' content='0;url=noscript'></noscript>";
	}

	
public function cekSessionLogin($page){
	if($page->Session->check('id')==true){
		header('location:mainmenus');exit();
	}
}

public function cekSession($page){
	if($page->Session->check('id')==false){
		header('location:index');exit();
	}
}

}
?>
