<?php
namespace App\Controller;

use App;
use App\Model\TempletModel;
use App\Lib\Helper\HUrl;

class TempletController extends Controller {
	protected $rules = array (
		'add'    => '@',
		'edit'   => '@',
		'delete' => '@'
	);
	
	function indexAction($limit) {
		$templet = new TempletModel();
		$this->show('', array(
			'templets' => $templet->findLimit($limit)
		));
	}
	
	function addAction() {
		$templet = new TempletModel();
		if (App::$request->isPost()) {
			$templet->fill(App::$request->post());
		}
		$this->show('', array());
	}
	
	function editAction($id) {
		$templet = new TempletModel();
		if (App::$request->isPost()) {
			$templet->updateById(App::$request->post(), $id);
		}
		
		$this->show('', array(
				'templet' => $templet->findById($id)
		));
	}
	
	function deleteAction($id) {
		$templet = new TempletModel();
		$templet->deleteById($id);
		App::redirect(HUrl::referer());
	}
	
	function ViewAction($id) {
		$templet = new TempletModel();
		$this->show('', array(
			'templet' => $templet->findById($id)
		));
	}
}