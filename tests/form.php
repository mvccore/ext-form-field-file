<?php

include_once("vendor/autoload.php");

$extBase = __DIR__."/../src/MvcCore/Ext/Forms/";
include_once($extBase . "Field/Props/Files.php");
include_once($extBase . "Fields/IFiles.php");
include_once($extBase . "Fields/File.php");
include_once($extBase . "Validators/IFiles.php");
include_once($extBase . "Validators/Files/CheckRequirements.php");
include_once($extBase . "Validators/Files/CompleteFiles.php");
include_once($extBase . "Validators/Files/ReadAccept.php");
include_once($extBase . "Validators/Files/Validations/FileAndSize.php");
include_once($extBase . "Validators/Files/Validations/NameAndExtension.php");
include_once($extBase . "Validators/Files/Validations/MimeTypeAndExtension.php");
include_once($extBase . "Validators/Files/Validations/IBombScanner.php");
include_once($extBase . "Validators/Files/Validations/BombScanners/PngImage.php");
include_once($extBase . "Validators/Files/Validations/BombScanners/ZipArchive.php");
include_once($extBase . "Validators/Files/Validations/BombScanners/GzArchive.php");
include_once($extBase . "Validators/Files/Validations/Bomb.php");
include_once($extBase . "Validators/Files.php");

class MicroApp {
	public static function Run ($controllerClass, $environmentName = \MvcCore\IEnvironment::PRODUCTION) {
		$app = \MvcCore\Application::GetInstance();
		$app->SetDebugClass('\MvcCore\Ext\Debugs\Tracy');
		$env = $app->GetEnvironment();
		$env->SetName($environmentName);
		$req = $app->GetRequest();
		$res = $app->GetResponse();
		call_user_func([$app->GetDebugClass(), 'Init']);
		$router = $app->GetRouter()->SetRequest($req);
		$router->Route();
		$route = $router->GetCurrentRoute();
		$action = $route ? $route->GetAction() : 'Index';
		$req->SetControllerName('');
		$req->SetActionName(
			\MvcCore\Tool::GetDashedFromPascalCase($action)
		);
		$ctrl = $controllerClass::CreateInstance()
			->SetApplication($app)
			->SetEnvironment($env)
			->SetRequest($req)
			->SetResponse($res)
			->SetRouter($router);
		try {
			$ctrl->Dispatch($action);
			$ctrl->Terminate();
		} catch (\Throwable $e) {
			\MvcCore\Debug::Exception($e);
		}
	}
}

class Ctrl extends \MvcCore\Controller {
	protected $layout = NULL;
	protected $viewScriptsPath = '../../../';
	public function IndexAction () {
		$this->view->form = $this->getForm();
		$this->setUpViewConfiguration();
	}
	public function SubmitAction () {
		$form = $this->getForm();
		list($result, $values, $errors) = $form->Submit();
		if ($result === \MvcCore\Ext\IForm::RESULT_SUCCESS) {
			//x($values['avatar']);
			$avatar = current($values['avatar']);
			$targetFullPath = $this->application->GetPathVar(TRUE) . '/' . $avatar->name;
			if (file_exists($targetFullPath))
				unlink($targetFullPath);
			move_uploaded_file(
				$avatar->tmpFullPath, $targetFullPath
			);
			$form->ClearSession();
		} else {
			x($values);
			x($errors);
		}
		$form->SubmittedRedirect();
	}
	public function SuccessAction () {
		$this->view->success = TRUE;
		$this->view->form = $this->getForm();
		$this->setUpViewConfiguration();
		$this->Render('index');
	}
	protected function setUpViewConfiguration () {
		$this->view->file_uploads			= @ini_get("file_uploads");
		$this->view->upload_max_filesize	= @ini_get("upload_max_filesize");
		$this->view->max_file_uploads		= @ini_get("max_file_uploads");
		$this->view->post_max_size			= @ini_get("post_max_size");
		$this->view->upload_tmp_dir			= @ini_get("upload_tmp_dir");
		$this->view->system_tmp				= \MvcCore\Tool::GetSystemTmpDir();
	}
	protected function getForm () {
		$form = (new \MvcCore\Ext\Form($this))
			->SetId('upload_test')
			->SetMethod(\MvcCore\IRequest::METHOD_POST)
			->SetEnctype(\MvcCore\Ext\IForm::ENCTYPE_MULTIPART)
			->SetAction($this->Url(':Submit'))
			->SetErrorUrl($this->Url(':Index'))
			->SetSuccessUrl($this->Url(':Success'));
		$avatar = (new \MvcCore\Ext\Forms\Fields\File)
			->SetMinCount(1)
			->SetMaxCount(20)
			->SetMaxSize('50MB')
			->SetAllowedFileNameChars(
				\MvcCore\Ext\Forms\Fields\File::ALLOWED_FILE_NAME_CHARS_DEFAULT
			)
			//->SetAccept(['image/*'])
			->SetAccept([
				'.zip', '.gzip', '.gz', '.xlsx', '.png', '.rar'
			])
			->SetName('avatar')
			->SetLabel('Avatar')
			->SetMultiple(TRUE)
			->SetRequired(TRUE);
		$send = (new \MvcCore\Ext\Forms\Fields\SubmitButton)
			->SetName('send')
			->SetValue('send');
		return $form->AddFields($avatar, $send);
	}
}

MicroApp::Run('Ctrl', \MvcCore\IEnvironment::DEVELOPMENT);