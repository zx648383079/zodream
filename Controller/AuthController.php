<?php
namespace App\Controller;
use App;
use App\Model\UserModel;
use App\Lib\Object\OArray;
class AuthController extends Controller{
	
	protected $rules = array(
		'logout' => '1',
		'register' => '!',
		'*' => '?'
	);
	/**
	*登陆界面
	*/
	function indexAction(){
		if( App::$request->isPost() )
		{
			$post = App::$request->post('email,pwd');
			$error = $this->validata( $post , array(
				'email' => 'email|required',
				'pwd' => 'min:6|required'
			));
			if(is_bool($error))
			{
				$user = new UserModel();
				$result = $user->findByUser( $post );
				if(!is_bool($result))
				{
					App::session('user', $result );
					if(App::$request->post('remember') == 1)
					{
						App::cookie('token' , $user->setToken($result), time() + 315360000 );
					}
					App::redirect('?c=home');
					exit;
				}else{
					$this->send(array(
					'error' => '邮箱不存在或密码有误！'
				));
				}
			}else{
				$this->send(array(
					'error' => OArray::tostring($error,',')
				));
			}
		}
		
		$this->show('login',array(
			'title' => '登录',
			'email' => App::$request->post('email')
		));
	}
	/**
	*扫码登录界面
	*/
	function qrcodeAction()
	{
		$this->send(array('title'=>'扫扫二维码','img'=>''));
		$this->show('qrcode');
	}
	/**
	*执行登出操作
	*/
	function logoutAction()
	{
		App::cookie('token', null);
		$id = App::session('user');
		$user = new UserModel();
		$user->clearToken($id);
		App::session('user', '');
		App::redirect('/?c=auth');
	}
	/**
	*注册界面
	*/
	function registerAction()
	{
		if(App::$request->isPost() )
		{
			$post = App::$request->post('name,email,pwd');
			$error = $this->validata( $post , array(
				'name' => 'required',
				'email' =>'unique:users|email|required',
				'pwd' => 'confirm:cpwd|min:6|required'
			));
			
			if(!is_bool($error))
			{
				$this->send(array(
					'error' => $error
				));
			}else{
				$user = new UserModel();
				$id = $user -> fillWeb( $post );
				App::session( 'user', $id );
				App::redirect('?c=home');
			}
		}
		
		$this->show('register',array(
			'title' => '注册',
			'name' => App::$request->post('name'),
			'email' => App::$request->post('email')
		));
	}
}