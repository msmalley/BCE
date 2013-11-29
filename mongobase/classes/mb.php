<?php

// Show errors
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

// Global MongoBase functions required :: v0.8
require_once(dirname(dirname(__FILE__)).'/functions/mb.php');

/**
 *
 * This is the core class for MongoBase
 * It contains lots of magical processes
 *
 */
class mongobase_mb extends mongobase_core
{

	public static $filters;

	protected static $env;
	private static $data;
	private static $template;

	protected static $options;

	/**
	 * This merges data from the defaults in __construct() with the ini file
	 * @param array $options
	 * @return array
	 */
	protected function options($options = array())
	{
		if(
			isset(parent::$ini[$options['key']])
			&& is_array(parent::$ini[$options['key']])
		){
			$settings = array_merge(parent::$ini[$options['key']], $options);
			unset($settings['key']);
			$this::$options = $settings;
			$this::$options['key'] = $options['key'];
		}
		else $this::$options = $options;
	}

	protected function process($contents = false, $type = 'urls')
	{
		$data = (array) $this::$env->urls;
		if($data && $contents)
		{
			foreach($data as $key => $value)
			{
				if(!isset($type))
				{
					$pattern = "/{{".$key."}}/i";
				}
				else
				{
					$pattern = "/{{".$type.'.'.$key."}}/i";
				}
				$contents = preg_replace($pattern, $value, $contents);
			}
			return $contents;
		};
		return false;
	}

	/**
	 * This creates the environment object used to defines paths and URLs
	 * @param type $options
	 * @param type $key
	 * @return stdClass
	 */
	private function environment($options = array(), $key = 'mb')
	{
		$defaults = array(
			'app'		=> dirname(dirname(parent::$app)),
			'mb'		=> dirname(dirname(parent::$mb)),
			'config'	=> dirname(parent::$config)
		);
		// Merge options and defaults
		$settings = array_merge($defaults, $options);

		// Set Classes
		$env = new stdClass();
		$env->paths = new stdClass();
		$env->urls = new stdClass();
		$env->ns = new stdClass();

		// Set Root
		$env->paths->root = parent::$root.'/';
		$full_path = dirname($_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']);
		$env->urls->root = str_replace($_SERVER['DOCUMENT_ROOT'], 'http://'.$_SERVER['SERVER_NAME'], $full_path.'/');
		if(!mb_ends_with($env->urls->root, '/')) $env->urls->root = $env->urls->root.'/';

		// Set Home
		$env->home = str_replace($_SERVER['DOCUMENT_ROOT'],'',$env->paths->root);

		// Get slug
		$root_length = strlen(parent::$root)+1;
		$slug  = $_SERVER['REQUEST_URI'];
		$env->slug = substr($_SERVER['DOCUMENT_ROOT'].$_SERVER['REQUEST_URI'],$root_length);
		if(substr($env->slug, -1) === '/') $env->slug = substr($env->slug,0,-1);
		if(mb_starts_with($env->slug, '/'))
		{
			$env->slug = substr($env->slug,1,  strlen($env->slug));
		}

		$env->slugged = $env->slug;
		if(mb_contains($env->slug, '?'))
		{
			$slug_array = explode('?', $env->slug);
			$env->slugged = $slug_array[0];
		}

		$slugs = explode('/', $env->slugged);
		$directory_array = array_slice($slugs,0,1);
		$env->dir = $directory_array[0];
		$env->actions = $slugs;

		// Set Paths
		$env->paths->app = $settings['app'].'/';
		$env->paths->mb = $settings['mb'].'/';
		$env->paths->config = $settings['config'].'/';

		// Set namespaces
		if(isset($this::$options['ns']['admin']))
		{
			$admin = $this::$options['ns']['admin'];
		}
        else $admin = _('admin');
		if(isset($this::$options['ns']['media']))
        {
			$media = $this::$options['ns']['media'];
		}
        else $media = _('media');
		if(isset($this::$options['ns']['api']))
        {
			$api = $this::$options['ns']['api'];
		}
        else $api = _('api');
		if(isset($this::$options['ns']['search']))
        {
			$search = $this::$options['ns']['search'];
		}
        else $search = _('search');
        if(isset($this::$options['ns']['ajax']))
        {
			$ajax = $this::$options['ns']['ajax'];
		}
        else $ajax = _('ajax');
		if(isset($this::$options['ns']['assets']))
        {
			$assets = $this::$options['ns']['assets'];
		}
        else $assets = _('assets');
        if(isset($this::$options['ns']['js']))
		{
			$js = $this::$options['ns']['js'];
		}
        else $js = _('js');
        if(isset($this::$options['ns']['css']))
        {
			$css = $this::$options['ns']['css'];
		}
        else $css = _('css');

		// Each of these represent a module:
		$env->ns->admin = $admin;
		$env->ns->media = $media;
		$env->ns->api = $api;
		$env->ns->ajax = $ajax;
		$env->ns->assets = $assets;

		$env->urls->media = $env->urls->root.$env->ns->media.'/';
		$env->urls->assets = $env->urls->root.$env->ns->assets.'/';

		$this::$env = $env;
	}

	private function admin()
	{
		$admin = mb_class('admin');
		if(is_object($admin))
		{
			return $admin->panels();
		}
	    else
		{
			return $this->pretty_wrapper('<h1>'._('Admin Module Missing!').'</h1>', false, 'mb-notification mb-error');
		}
	}

	private function media()
	{
		$media = mb_class('media');
		if(is_object($media))
		{
			$file = null;
			if(isset($_FILES['file']))
			{
				$file = $_FILES['file'];
			}
			if(isset($_GET['action']) && isset($_GET['uid']) && isset($_GET['nonce']) && $_GET['action'] == 'upload' && isset($file))
			{
				return $media->upload($file, $_GET['uid'], $_GET['col'], $_GET['field'], $_GET['uid'], $_GET['nonce']);
			}
			else
			{
				$grid_id = $this::$env->actions[1];
				$media->show($grid_id);
			}
		}
	    else
		{
			return $this->pretty_wrapper('<h1>'._('Media Module Missing!').'</h1>', false, 'mb-notification mb-error');
		}

	}

	private function assets()
	{
		$assets = mb_class('assets');
		if(is_object($assets))
		{
			return $assets->asset();
		}
	    else
		{
			return $this->pretty_wrapper('<h1>'._('Assets Module Missing!').'</h1>', false, 'mb-notification mb-error');
		}

	}

	private function api()
	{
		$api = mb_class('api');
		if(is_object($api))
		{
			$action = $this::$env->actions[1];
			if(method_exists($api, $action)) return $api->$action($_POST);
			else return $this->pretty_wrapper('<h1>'._('Un-Supported API METHOD!').'</h1>', false, 'mb-notification mb-error');
		}
	    else
		{
			return $this->pretty_wrapper('<h1>'._('API Module Missing!').'</h1>', false, 'mb-notification mb-error');
		}

	}

	private function ajax()
	{
		$ajax = mb_class('ajax');
		if(is_object($ajax))
		{
			return $ajax->check();
		}
	    else
		{
			return $this->pretty_wrapper('<h1>'._('AJAX Module Missing!').'</h1>', false, 'mb-notification mb-error');
		}
	}

	protected function cascade($directory = false, $file = false, $extension = 'php', $default_content = false, $precision = false, $return_url = false)
	{
		$contents = $default_content;
		if(isset($this::$env) && $directory && $file && $extension)
		{
			$slug = $this::$env->slug;
			$dir = $this::$env->dir;
			$root_folder = $this::$env->paths->root;
			$default_app_folder = $root_folder.'mb_app/';
			$app_folder = $this::$env->paths->app;
			$mb_folder = $this::$env->paths->mb;
			if($app_folder && $mb_folder)
			{
				$content = false;

				// Check slug-relevant folders first
				if(file_exists($default_app_folder.$directory.'/'.$slug.'/'.$file.'.'.$extension))
				{
					$content = $default_app_folder.$directory.'/'.$slug.'/'.$file.'.'.$extension;
				}
				elseif(file_exists($app_folder.$directory.'/'.$slug.'/'.$file.'.'.$extension))
				{
					$content = $app_folder.$directory.'/'.$slug.'/'.$file.'.'.$extension;
				}
				elseif(file_exists($mb_folder.$directory.'/'.$slug.'/'.$file.'.'.$extension))
				{
					$content = $mb_folder.$directory.'/'.$slug.'/'.$file.'.'.$extension;
				}

				// Check standard folders
				elseif(file_exists($default_app_folder.$directory.'/'.$file.'.'.$extension))
				{
					$content = $default_app_folder.$directory.'/'.$file.'.'.$extension;
				}
				elseif(file_exists($app_folder.$directory.'/'.$file.'.'.$extension))
				{
					$content = $app_folder.$directory.'/'.$file.'.'.$extension;
				}
				elseif(file_exists($mb_folder.$directory.'/'.$file.'.'.$extension))
				{
					$content = $mb_folder.$directory.'/'.$file.'.'.$extension;
				}

				// Check directory folders next
				elseif(file_exists($default_app_folder.$directory.'/'.$dir.'.'.$extension))
				{
					$content = $default_app_folder.$directory.'/'.$dir.'.'.$extension;
				}
				elseif(file_exists($app_folder.$directory.'/'.$dir.'.'.$extension))
				{
					$content = $app_folder.$directory.'/'.$dir.'.'.$extension;
				}
				elseif(file_exists($mb_folder.$directory.'/'.$dir.'.'.$extension))
				{
					$content = $mb_folder.$directory.'/'.$dir.'.'.$extension;
				}

				// Re-check true default app folder?
				elseif(file_exists($mb_folder.'app/'.$directory.'/'.$file.'.'.$extension))
				{
					$content = $mb_folder.'app/'.$directory.'/'.$file.'.'.$extension;
				}

				if($precision)
				{
					if(file_exists($default_app_folder.$directory.'/'.$file.'.'.$extension))
					{
						$content = $default_app_folder.$directory.'/'.$file.'.'.$extension;
					}
					elseif(file_exists($app_folder.$directory.'/'.$file.'.'.$extension))
					{
						$content = $app_folder.$directory.'/'.$file.'.'.$extension;
					}
					elseif(file_exists($mb_folder.$directory.'/'.$file.'.'.$extension))
					{
						$content = $mb_folder.$directory.'/'.$file.'.'.$extension;
					}
				}
				if(file_exists($content))
				{
					if($return_url)
					{
						return $content;
					}
					else
					{
						if($extension != 'php')
						{
							$contents = file_get_contents($content);
						}
						else
						{
							$use_hotfix = false;

							if($use_hotfix)
							{
								$key = 'mb_cascade_'.$directory.$file.$extension;
								if(isset($GLOBALS[$key]))
								{
									$contents = $GLOBALS[$key];
								}
								else
								{
									ob_start();
									include_once($content);
									$contents = ob_get_clean();
									$GLOBALS[$key] = $contents;
								}
							}
							else
							{
								ob_start();
								include($content);
								// include_once prevent admins
								$contents = ob_get_clean();
							}
						}
					}
				}
			}
		};
		if($directory == 'data') $contents = (array) json_decode($contents, true);
		return $contents;
	}

	private function routes($current_slug = false)
	{
		if(isset($this::$ini) && isset($this::$ini['route']) && isset($this::$env) && isset($this::$env->slug))
		{
			$slug = $this::$env->slug;
			$slugged = $this::$env->slugged;
			$routes = $this::$ini['route'];
			if(is_array($routes) && count($routes) > 0)
			{
				foreach($routes as $routed_slug => $routed_value)
				{
					if($slugged == $routed_slug) $slugged = $routed_value;
				}
			}
			$current_slug = $slugged;
		};
		return $current_slug;
	}

	protected function css($options = array())
	{
		$current_slug = $this::$env->slugged;
		if(!$current_slug) $current_slug = 'index';
		$essentials = array(
			'less'			=> 'less',
			'forms'			=> 'form_add_ons',
		);
		$defaults = array(
			$this::$env->dir	=> $this::$env->dir,
			$current_slug		=> $current_slug,
			$this::$id			=> $this::$id
		);
		$settings = array_merge($defaults, $options);

		// Ignore app name classes in admin panel!
		$admin = $this::$env->ns->admin;
		if($this::$env->dir == $admin)
		{
			unset($defaults[$this::$id]);
		}

		$css = false;

		$root = $this::$env->urls->root;

		if(count($essentials) > 0){
			foreach($essentials as $key => $contents)
			{
				if($this->cascade('assets', 'css/'.$contents, 'css')) $css.= "<link id='css-$key' href='{$root}assets/css/$contents.css' rel='stylesheet'>\n";
			}
		}

		if(count($options) > 0){
			foreach($options as $key => $contents)
			{
				if($this->cascade('assets', 'css/'.$contents, 'css')) $css.= "<link id='css-$key' href='{$root}assets/css/$contents.css' rel='stylesheet'>\n";
			}
		}

		if(count($defaults) > 0){
			foreach($defaults as $key => $contents)
			{
				if($this->cascade('assets', 'css/'.$contents, 'css')) $css.= "<link id='css-$key' href='{$root}assets/css/$contents.css' rel='stylesheet'>\n";
			}
		}
		return $css;
	}

	private function js($options = array())
	{
		$current_slug = $this::$env->slugged;
		if(!$current_slug) $current_slug = 'index';
		$essentials = array(
			'jquery'		=> 'jquery',
			'libs'			=> 'libs',
			'pretty'		=> 'pretty',
			'common'		=> 'common',
			'drop'			=> 'dropzone',
			'bootstrap'		=> 'bootstrap'
		);
		$defaults = array(
			$this::$env->dir	=> $this::$env->dir,
			$current_slug		=> $current_slug,
			$this::$id			=> $this::$id
		);
		$settings = array_merge($defaults, $options);

		// Ignore app name classes in admin panel!
		$admin = $this::$env->ns->admin;
		if($this::$env->dir == $admin)
		{
			unset($defaults[$this::$id]);
		}

		$js = false;

		$root = $this::$env->urls->root;

		if(count($essentials) > 0){
			foreach($essentials as $key => $contents)
			{
				if($this->cascade('assets', 'js/'.$contents, 'js')) $js.= "<script id='js-$key' src='{$root}assets/js/$contents.js'></script>\n";
			}
		}

		if(count($options) > 0){
			foreach($options as $key => $contents)
			{
				if($this->cascade('assets', 'js/'.$contents, 'js')) $js.= "<script id='js-$key' src='{$root}assets/js/$contents.js'></script>\n";
			}
		}

		if(count($defaults) > 0){
			foreach($defaults as $key => $contents)
			{
				if($this->cascade('assets', 'js/'.$contents, 'js')) $js.= "<script id='js-$key' src='{$root}assets/js/$contents.js'></script>\n";
			}
		}
		return $js;
	}

	private function token($token, $refer)
	{
		$time = date('g A', strtotime('today'));
		$hash = hash('sha256', $time);

		$root_url = $this::$env->urls->root;
		$slug = $this::$env->slug;

		if($root_url.$slug == $refer)
		{
			$hash = hash('sha256', $slug.'_'.$time.'_'.$root_url);
		}
		return $hash;
	}

	private function default_data()
	{
		return array(
			'title'	=> $this->get_option('title', _('MongoBasee'))
		);
	}

	private function template($options = array())
	{
		$defaults = array(
			'engine' => 'mustache'
		);
		// Merge options and defaults
		$settings = array_merge($defaults, $options);

		// Engine extremely important
		if(!isset($settings['engine'])) $engine = 'mustache';
		else $engine = $settings['engine'];

		// Get slug
		$current_slug = false;
		if(isset($this::$env)) $current_slug = $this::$env->slugged;
		if(!$current_slug) $current_slug = 'index';

		// Get default data and slug based data then merge
		$slug_data = $this->cascade('data', $this->routes($current_slug));
		if(!is_array($slug_data)) $slug_data = array();
		$app_data = $this->cascade('data', $this->get_option('id'));
		if(!is_array($app_data)) $app_data = array();
		$data = array_merge($app_data, $slug_data);
		if(!isset($this::$data)) $data;

		if(isset($this::$env) && count($this::$env) > 0)
		{
			foreach($this::$env->urls as $key => $value)
			{
				$data['urls'][$key] = $value;
			}
		}

		// Get template
		$template = $this->cascade('templates', $this->routes($current_slug));

		// Dirty hack?
		if($this::$id === false)
		{
			if(isset($this::$ini['mb']) && isset($this::$ini['mb']['id'])) $this::$id = $this::$ini['mb']['id'];
			else $this::$id = 'documentation';
		}
		if(!$template) $template = $this->cascade('templates', 'index');

		// Render and return HTML
		$template_engine = mb_class($engine);
		$html = $template_engine->render(array(
			'content'	=> $template,
			'data'		=> $data
		));

		// Need to turn highjack into function
		$slug = $this::$env->slug;
		if(mb_starts_with($slug, '?reset=mbr'))
		{
			$db = mb_class('db');
			$users = $db->find(array('col'=>'users'));
			if(is_array($users))
			{
				$auth = mb_class('auth');
				$user_salt = $auth->get_user_salt();
				$timestamp = date('d m Y H') . ':00';
				foreach($users as $user)
				{
					$email = $user['e'];
					$password = $user['p'];
					$id = $db->_id($user['_id']);
					$hash = hash('sha256', $id.$email.$password.$user_salt.$timestamp);
					if($slug == '?reset=mbr'.$hash)
					{
						$form = mb_class('forms');
						return $form->reset(array('id'=>$id));
						exit;
					}
				}
				$form = mb_class('forms');
				return $form->login(array(
					'title' => _('Password Reset Expired'),
					'intro' => _('Please re-enter your email address and send the link again.')
				), true);
			}
		}
		elseif(mb_starts_with($slug, '?invite=mbi'))
		{
			$db = mb_class('db');
			$signups = $db->find(array('col'=>'signups'));
			if(is_array($signups))
			{
				$got_one = false;
				$auth = mb_class('auth');
				$user_salt = $auth->get_user_salt();
				foreach($signups as $signup)
				{
					$acl = 8;
					$email = $signup['e'];
					$id = $db->_id($signup['_id']);
					$hash = hash('sha256', $email.$user_salt.$id);
					if($slug == '?invite=mbi'.$hash)
					{
						$got_one = true;
						$valid_email = $email;
						if(isset($signup['acl'])) $acl = $signup['acl'];
					}
				}
				if($got_one)
				{
					$forms = mb_class('forms');
					return $forms->register(array('email'=>$valid_email,'acl'=>$acl,'hash'=>$hash));
				}
				else
				{
					return $this->pretty_wrapper('<h1>'._('Invalid Invite Code').'</h1>', false, 'mb-notification mb-error');
				}
			}
		}
		else
		{
			$this::$template = $html;
			return $html;
		}
	}

	private function content($specific_title = false, $specific_content = false)
	{
		$title = $this->get_option('title', _('MongoBase'));
		
		if($specific_title) $title = $specific_title;
		$content = $this->template();

		if($specific_content) $content = $specific_content;

		$configured_js = array();
		if(isset($this::$ini['js']) && is_array($this::$ini['js']) && count($this::$ini['js']) > 0)
		{
			foreach($this::$ini['js'] as $handle => $script_name)
			{
				$configured_js[$handle] = $script_name;
			}
		}

		$configured_css = array();
		if(isset($this::$ini['css']) && is_array($this::$ini['css']) && count($this::$ini['css']) > 0)
		{
			foreach($this::$ini['css'] as $handle => $script_name)
			{
				$configured_css[$handle] = $script_name;
			}
		};

		$nonce = false;
		if(mb_class_loaded('auth'))
		{
			$auth = mb_class('auth');
			$nonce = $auth->nonce();
		}

		$token = $this::$env->slug.'_'.date('g A', strtotime('today')).'_'.$this::$env->urls->root;

		$sid = session_id();
		if(!$sid)
		{
			session_start();
			$sid = session_id();
		}

		$html = parent::html(array(
			'attributes'	=> array(
				'html'			=> false,
				'body'			=> ' '.
					'data-spy="scroll" '.
					'data-target=".mb-spy" '.
					'data-mb-url="'.$this::$env->urls->root.'" '.
					'data-nonce="'.$nonce.'" '.
					'data-token="'.$this->token($token, $this::$env->urls->root.$this::$env->slug).'" '.
					'data-slug="'.$this::$env->slug.'" '.
					'data-dir="'.$this::$env->dir.'" '.
					'data-session="'.$sid.'" '.
					'class="slug_'.$this::$env->slug.' dir_'.$this::$env->dir.'" ',
				'charset'		=> _('utf-8'),
				'meta'			=> null,
			),
			'title'				=> $title,
			'favicon'			=> $this::$env->urls->root.'favicon.ico',
			'files'			=> array(
				'js'			=> $this->js($configured_js),
				'less'			=> null,
				'css'			=> $this->css($configured_css)
			),
			'content'		=> $content
		));

		return $html;
	}

	private function route()
	{
	    $method_name = false;
	    $slug = $this::$env->slug;
	    $protected_urls = $this::$env->ns;
	    foreach($protected_urls as $key => $url)
	    {
			if(mb_starts_with($slug, $url)) $method_name = $key;
	    }
		$method_name = $this->apply_filters('mb_route', $method_name);
	    return $method_name;
	}

	/**
	 * This initiates required core processes
	 * @param array $options
	 * @param key $key : This is used as a reference to config.ini dimensions
	 */
	function __construct($options = array(), $key = 'mb')
	{
		$defaults = array(
			'key'		=> $key
		);
		// Merge options and defaults
		$settings = array_merge($defaults, $options);

		if(!$this::$options) $this->options($settings);
		if(!$this::$env) $this->environment($settings);
	}

	public function get_option($field = false, $default = false, $key = false)
	{
		$options = $this::$options;
		if($key && isset($this::$ini[$key]))
		{
			if(isset($this::$ini[$key][$field])) return $this::$ini[$key][$field];
			else return $this::$ini[$key];
		}
		elseif($key)
		{
			if(isset($this::$options[$key]) && isset($this::$options[$key][$field])) return $this::$options[$key][$field];
			else return $default;
		}
		else
		{
			if(isset($this::$options[$field])) return $this::$options[$field];
			else return $default;
		}
	}

	public function add_filter($key, $function)
	{
		self::$filters[$key] = $function;
	}

	public function pretty_wrapper($content = false)
	{
		$html = '<div class="pretty-print-wrapper">'.$content.'</div>';
		return $html;
	}

	public function apply_filters($key, $arg, $args = false, $more_args = false)
	{
		if(!isset(self::$filters)) self::$filters = array();
		if(isset(self::$filters[$key])) {
			$function_name = self::$filters[$key];
			if(function_exists($function_name)){
				$filtered_content = call_user_func($function_name, $arg, $args, $more_args);
				return $filtered_content;
			}else{
				return $arg;
			}
		}else{
			return $arg;
		}
	}

	public function display()
	{
		$function = $this->route();
		if(method_exists($this, $function)){
			$contents = $this->content(false, $this->$function());
		}
		else
		{
			$contents = $this->content();
		}
		echo $contents;
	}

	public function error($message, $exit = true)
	{
		trigger_error($message, E_USER_NOTICE);
		if($exit) exit;
	}
}
