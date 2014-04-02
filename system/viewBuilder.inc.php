<?php
class viewBuilder {

	public $core;

	public $id;
	public $settings;
	public $viewConfig;

	public function __construct($core) {
		$this->core = $core;

		$this->viewConfig->title = TRUE;
		$this->viewConfig->description = TRUE;
		$this->viewConfig->open = FALSE;
		$this->viewConfig->header = TRUE;
		$this->viewConfig->breadcrumb = TRUE;
		$this->viewConfig->footer = TRUE;
		$this->viewConfig->menu = TRUE;
		$this->viewConfig->javascript = array();
		$this->viewConfig->css = array();
	}

	public function buildView($page, $external = FALSE) {
		$action = $this->core->action;
		$this->core->logEvent("Starting view builder for page: " . $this->core->page . " action: " . $this->core->action, "3");

		if (empty($page)) {
			if ($this->core->role > 0) {
				$this->initView("home");
				$page = "home";
			} else {
				$this->initView("login");
				$page = "login";
			}
		} elseif ($page == "login") {
			$auth = new auth($this->core);
			$login = $auth->login();
			if($login == FALSE){
				$this->core->setViewError('Login failed', 'Please <a href=".">return to the login page</a> and try again. If you forgot your password please request a new one <a href="password.php">here</a>.');
				$this->initView("error", NULL);
				$this->processView("showError", NULL);
				return;
			} else if($login == TRUE) {
				$this->core->redirect("home", "show", NULL);
			}
		} elseif ($page == "download") {
			$filename = $this->core->cleanGet['file'];
			include $this->core->conf['conf']['classPath'] . "files.inc.php";
			downloadFile($filename);
		} elseif ($page == "logout") {
			auth::logout();
			$this->core->redirect(NULL, NULL, NULL);
		} elseif ($page == "template") {
			$this->core->setTemplate();
		} else {
			$this->initView($page);
		}

		if(empty($action)){
			$action = "show";
		}

		$function = $action . ucwords($page);

		if(method_exists($this->view, $function)){
			$this->core->logEvent("Function exists, loading settings: function: " . $function, "3");
			$viewsettings = $this->viewConfig;

			$this->settings = $this->core->getFunctionSettings($page, $action, $viewsettings);

			if($external != TRUE){
				foreach($this->settings->functionRequiredElements as $config => $value){
					$this->viewConfig->$config = $value; 
				}
			}
			
			if($this->core->role >= $this->settings->minRole && $this->core->role <= $this->settings->maxRole || $this->core->role == 1000){
				$this->processView($function, $this->settings);
			} else {
				$this->core->setViewError('Insufficient privileges', 'Please <a href="'.$this->core->conf['conf']['path'].'">return to the home page</a> and try again.');
				$this->initView("error", NULL);
				$this->processView("showError", NULL);
			}
		} else {
			$this->core->setViewError('Error 404: Page not found', 'Please <a href=".">return to the login page</a> and try again. If you forgot your password please request a new one <a href="password.php">here</a>.');
			$this->initView("error");
			$this->processView("showError", NULL);
		}

	}

	public function initView($view, $external = FALSE) {
		$viewInclude = $this->core->conf['conf']['viewPath'] . $view . ".view.php";

		if (file_exists($viewInclude)) {
			$this->core->logEvent("Initializing view $view", "3");

			require_once $viewInclude;

			$this->view = new $view();
			
		} else {
			$this->core->throwError("Required view missing $viewInclude");
		}

		$output = json_encode($this->viewConfig);

		return $this->viewConfig; 
	}

	public function processView($function, $settings = NULL) {
		$this->headerIncludes();

		if ($this->viewConfig->header == TRUE) {
			echo $this->core->component->generateHeader();
		}

		if ($this->viewConfig->menu == TRUE) {
			echo $this->core->component->generateMenu();
		}

		$view = $this->view->buildview($this->core);

		if ($this->viewConfig->breadcrumb == TRUE) {
			echo $this->core->component->generateBreadcrumb($this->viewConfig);
		}

		if ($this->viewConfig->title == TRUE) {
			echo $this->core->component->generateTitle($settings->title);
		}

		if ($this->viewConfig->description == TRUE) {
			echo $this->core->component->generateDescription($settings->description);
		}

		$run = $this->view->$function($this->core->item);

		if ($this->core->conf['conf']['debugging'] == TRUE) {
			$this->core->showDebugger();
		}

		if ($this->viewConfig->footer == TRUE) {
			echo $this->core->component->generateFooter();
		}
	}

	private function headerIncludes(){
		foreach ($this->core->conf['javascript']['required'] as $required){
			array_unshift($this->viewConfig->javascript, $required);
		}

		foreach ($this->core->conf['css']['required'] as $required){
			array_unshift($this->viewConfig->css, $required);
		}

		if ($this->viewConfig->menu != FALSE || !isset($this->viewConfig->menu)) {
			array_push($this->viewConfig->javascript, 'mail');
		}

		foreach ($this->viewConfig->javascript as $file) {
			$this->core->component->jsFiles .= $this->core->conf['javascript'][$file];
		}

		foreach ($this->viewConfig->css as $file) {
			$this->core->component->cssFiles .= $this->core->conf['css'][$file] . "\n";
		}

		$this->core->component->cssFiles = str_replace("%BASE%", $this->core->conf['conf']['path'], $this->core->component->cssFiles);
		$this->core->component->jsFiles = str_replace("%BASE%", $this->core->conf['conf']['path'], $this->core->component->jsFiles);
		$this->core->component->cssFiles = str_replace("%TEMPLATE%", $this->core->template, $this->core->component->cssFiles);
	}

}

?>
