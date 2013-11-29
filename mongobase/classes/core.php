<?php

global $mb;

/**
 *
 * Version 0.6.2
 *
 * This class sets the globals whilst locating config, core and app files.
 *
 * Please note that a unit test is available for this class at:
 * root/mb_tests/MongobaseBootstrapTest.php
 *
 */
class mongobase_core
{

	protected static $ini;

	public static $id;
	public static $root;
	public static $config;
	public static $mb;
	public static $app;
	public static $requirements;

	protected function modal($options = array())
	{
		$defaults = array(
			'id'		=> false,
			'header'	=> false,
			'body'		=> false,
			'button'	=> false,
			'close'		=> $this->__('OK')
		); $settings = array_merge($defaults, $options);
		$id = $settings['id'];
		$header = $settings['header'];
		$body = $settings['body'];
		$button = $settings['button'];
		$close = $settings['close'];
		$modal = false;
		$modal.= '<div class="modal fade" id="mb_modal_'.$id.'" tabindex="-1" role="dialog" aria-labelledby="mb_modal_'.$id.'_label" aria-hidden="true">';
			$modal.= '<div class="modal-dialog">';
			$modal.= '<div class="modal-content">';
			$modal.= '<div class="modal-header" style="min-height: 50px;">';
				$modal.= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
				$modal.= '<h4 id="mb_modal_'.$id.'_label" style="right: 40px; position:absolute; left:20px; top:0; line-height: 30px;">'.$header.'</h4>';
			$modal.= '</div>';
			$modal.= '<div class="modal-body">';
				$modal.= '<p>'.$body.'</p>';
			$modal.= '</div>';
			$modal.= '<div class="modal-footer">';
				$modal.= '<button class="btn closer" data-dismiss="modal" aria-hidden="true">'.$close.'</button>';
				$modal.= '<button class="btn btn-primary">'.$button.'</button>';
			$modal.= '</div>';
			$modal.= '</div>';
			$modal.= '</div>';
		$modal.= '</div>';
		return $modal;
	}

	/**
	 * Minium environment requirments needed for MongoBase
	 * @param array $options
	 * @return mixed : True if passed else exit with pretty_print()
	 */
	protected function requirements($options = array(), $ini = false)
	{
		$defaults = array(
			'php'		=> 5.3,
		);
		// Merge options and defaults
		$settings = array_merge($defaults, $options);
		$use_db = false;

		if(isset($ini['db']))
		{
			$use_db = true;
		}

		$errors = false;
		foreach($settings as $setting_name => $setting_value)
		{
			$continue = true;
			if($setting_name == 'mongodb') $continue = false;
			if($setting_name == 'mongodb' && $use_db === true) $continue = true;
			if($continue)
			{
				$this::$requirements[$setting_name] = $setting_value;
				$current_function_name = 'check_'.$setting_name;
				$got_correct_version = $this->$current_function_name($setting_value);
				if(!$got_correct_version)
				{
					$errors = '<h1>'._('UNABLE TO CONTINUE').'</h1>';
					$errors.= sprintf('<p>'._('%1$s %2$s required').'</p>', $setting_name, $setting_value);
				}
			}
		}
		if($errors) $this->pretty_print(_('Error'), $errors);
		else return true;
	}

	/**
	 * Check if PHP version matches
	 * @param int $required : 999 is for emergencies (and sanity)
	 * @return boolean
	 */
	private function check_php($required = 999)
	{
		$current_php_version = floatval(PHP_VERSION);
		if($current_php_version < $required) return false;
		else return true;
	}

	/**
	 * Check if MongoDB version matches
	 * @param int $required : 999 is for emergencies (and sanity)
	 * @return boolean
	 */
	private function check_mongodb($required = 999)
	{
		// TODO: Existence of [db] in config.ini should define if DB is needed!
		try{
			$m = new Mongo();
			$adminDB = $m->admin; //require admin priviledge
			$mongodb_info = $adminDB->command(array('buildinfo'=>true));
			$current_mongodb_version = (float)$mongodb_info['version'];
			if($current_mongodb_version < $required) return false;
			else return true;
		}catch(MongoConnectionException $e){
			return null;
		}catch(MongoException $e) {
			return null;
		}catch(MongoCursorException $e) {
			return null;
		}catch(Exception $e) {
			return false;
		};
		return null;
	}

	/**
	 * Check if PHP MongoDB Driver version matches
	 * @param int $required : 999 is for emergencies (and sanity)
	 * @return boolean
	 */
	private function check_mphp($required = 999)
	{
		$current_mongo_php_driver_version = MONGO::VERSION;
		if($current_mongo_php_driver_version < $required) return false;
		else return true;
	}

	/**
	 * Search for configuration file and set path to globals
	 * @param array $options
	 * @param int $defaults
	 * @return boolean
	 */
	private function set_config($options = array(), $sanity = 10)
	{
		$defaults = array(
			'config'		=> 'mb_config/config.ini',
			'recursion'		=> 3,
			'sanity'		=> (int) $sanity
		);
		// Merge options and defaults
		$settings = array_merge($defaults, $options);

		// Recursion important and requires sanity check of 10
		if(!isset($settings['recursion'])) $settings['recursion'] = 0;
		if( (int) $settings['recursion'] > (int) $settings['sanity'])
		{
			$settings['recursion'] = $settings['sanity'];
		}

		// Set basics
		$this::$root = dirname(__FILE__);
		$this::$config = $this::$root.'/'.$settings['config'];
		$config_folder = $this::$root.'/'.$settings['config'];

		// Start recursion
		$recursive_count = 0;
		while($recursive_count <= (int) $settings['recursion'])
		{
			$config_folder = dirname($config_folder).'/';
			if(file_exists($config_folder.$settings['config']))
			{
				$this::$config = $config_folder.$settings['config'];
				$this::$ini = parse_ini_file($config_folder.$settings['config'], true);
				$recursive_count = (int) $settings['recursion'];
			}
			$recursive_count++;
		};

		// Ensure ini is set and return its status
		if(!isset($this::$ini))
		{
			$this::$ini = array();
			$this::$id = false;
			return false;
		}
		else
		{
			$ready = $this->requirements(array(), $this::$ini);
			if($ready)
			{
				$app_id = $this::$ini['mb']['id'];
				$this::$id = $app_id;
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Search for core file and set path to globals
	 * @param array $options
	 * @param array $defaults
	 * @return boolean
	 */
	private function set_core($options = array())
	{
		$defaults = array(
			'core'		=> 'mb_core/classes/mb.php'
		);
		// Merge options and defaults
		$settings = array_merge($defaults, $options);

		// Set core file
		$core_file = $this::$root.'/'.$settings['core'];
		if(isset($this::$ini['mb']['core'])) $core_file = $this::$ini['mb']['core'];

		// Check for local_core
		if($_SERVER['SERVER_NAME'] == 'localhost' && isset($this::$ini['mb']['local_core']))
		{
			$core_file = $this::$ini['mb']['local_core'];
		}

		$this::$root = dirname(__FILE__);

		// Get core file and return results
		if(file_exists($core_file)){
			$this::$mb = $this::$root.'/'.$core_file;
			return true;
		}else{
			$this::$mb = false;
			return false;
		}
	}

	/**
	 *
	 * Search for app file and set path to globals
	 * If necessary folders are not found at mb_app, we look in mb_core/app
	 *
	 * @param array $options
	 * @return boolean
	 *
	 */
	private function set_app($options = array())
	{
		if($this::$id === false)
		{
			if(isset($this::$ini['mb']) && isset($this::$ini['mb']['id'])) $this::$id = $this::$ini['mb']['id'];
			else $this::$id = 'documentation';
		}

		$defaults = array(
			'app'			=> 'mb_app/classes/'.$this::$id.'.php',
			'functions'		=> 'mb_app/functions/'.$this::$id.'.php'
		);
		// Merge options and defaults
		$settings = array_merge($defaults, $options);

		// Set app file
		$functions = $this::$root.'/'.$settings['functions'];
		$app_file = $this::$root.'/'.$settings['app'];
		if(isset($this::$ini['mb']['functions'])) $functions = $this::$ini['mb']['functions'];
		if(isset($this::$ini['mb']['app'])) $app_file = $this::$ini['mb']['app'];

		// Set functions
		if(file_exists($functions))
		{
			include_once($functions);
		}

		// Get app file and return results
		if(file_exists($app_file))
		{
			$this::$app = $app_file;
			return true;
		}
		else
		{
			// Fall-back to default app in core folder if app missing
			$app_file = dirname(dirname($this::$mb)).'/app/classes/'.$this::$id.'.php';
			if(file_exists($app_file))
			{
				$this::$app = $app_file;
				return true;
			}
			else
			{
				$this::$app = false;
				return false;
			}
		}
	}

	/**
	 * Attempts to find config, core and app and set paths to global
	 */
	function __construct(){
		$error = false;
		$configured = $this->set_config();
		if($configured)
		{
			$core_located = $this->set_core();
			if($core_located)
			{
				$app_found = $this->set_app();
				if($app_found)
				{
					$GLOBALS['mb']['paths']['config'] = $this::$config;
					$GLOBALS['mb']['paths']['core'] = $this::$mb;
					$GLOBALS['mb']['paths']['app'] = $this::$app;
					if(file_exists($this::$mb)) require_once($this::$mb);
					if(file_exists($this::$app)) require_once($this::$app);
				}
				else
				{
					$error = _('MB App Missing!');
				}
			}
			else
			{
				$error = _('MB Core Missing!');
			}
		}
		else
		{

			// No configuration file - is it really needed now...?
			// TODO: Answer this question!

			$core_located = $this->set_core();
			if($core_located)
			{

				$app_found = $this->set_app();
				// Only difference is lack of app class...
				$GLOBALS['mb']['paths']['config'] = $this::$config;
				$GLOBALS['mb']['paths']['core'] = $this::$mb;
				$GLOBALS['mb']['paths']['app'] = $this::$app;
				if(file_exists($this::$mb)) require_once($this::$mb);
				if(file_exists($this::$app)) require_once($this::$app);

			}
			else
			{
				$error = _('MB Core Missing!');
			}

		}
		if($error)
		{
			$title = _('Error');
			$this->pretty_print($title, $error);
		}
	}

	/**
	 *
	 * Easy way to print a simple but stylish error page
	 * Please note that this function will exit code
	 * It constructs a full HTML page
	 *
	 * @param string $title = Title of the page
	 * @param string $content = Contents within the pretty_print_wrapper
	 *
	 */
	public function pretty_print($title = false, $content = null)
	{
		if(!isset($content)) $content = _('Hello World');

		// TODO: REPLACE THIS WITH RESET.CSS
		$html = '<style> body, html { background: #F5F5F6; font-family: sans-serif; } .pretty_print_wrapper { display: block; background: #FFF; border: 1px solid #DDD; width: 70%; padding: 40px 20px; margin: 20px auto; text-align: center; font-family: sans-serif; } #mb_modal_default { display: none; }</style>';

		// Create and echo HTML
		$html.= '<div class="pretty_print_wrapper">'.$content.'</div>';
		echo $this->html(array(
			'title'		=> $title,
			'content'	=> $html
		));

		// This should be the very final thing done...
		exit;
	}

	/**
	 * This function constructs a HTML page and returns the page as a string
	 * @param array $options
	 * @return string
	 */
	public function html($options = array())
	{
		$defaults = array(
			'attributes'	=> array(
				'html'		=> false,
				'body'		=> false,
				'charset'	=> _('utf-8'),
				'meta'		=> null
			),
			'title'			=> false,
			'favicon'		=> _('favicon.ico'),
			'js_lang'		=> false,
			'files'			=> array(
				'js'		=> null,
				'less'		=> null,
				'css'		=> null
			),
			'content'		=> false
		);
		// Merge options and defaults
		$settings = array_merge($defaults, $options);

		if(isset($settings['attributes']['html'])) $html_attributes = $settings['attributes']['html'];
		else $html_attributes = false;

		if(isset($settings['attributes']['body'])) $body_attributes = $settings['attributes']['body'];
		else $body_attributes = false;

		if(isset($settings['attributes']['charset'])) $charset = $settings['attributes']['charset'];
		else $charset = false;

		if(isset($settings['title'])) $title = $settings['title'];
		else $title = _('MongoBase');

		if(isset($settings['content'])) $content = $settings['content'];
		else $content = _('Hello World');

		// Start HTML Construction
		$html = false;
		$html.= "<!doctype html>\n";
		$html.= "<html$html_attributes>\n";
		$html.= "<head>\n\n";
		$html.= "<meta charset='$charset' />\n";
		$html.= "<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";

		// Sort meta-tags
		if(isset($settings['attributes']['meta']))
		{
			$html.= $this->meta($settings['attributes']['meta']);
		}

		// Add page title and favicon
		if($this->set_app())
		{
			$title = $this->apply_filters('mb_core_title', $title);
		}
		else
		{
			if(isset($this::$ini) && isset($this::$ini['mb']) && isset($this::$ini['mb']['title']))
			{
				$title = $this::$ini['mb']['title'];
			}
		}
		$html.= "<title>".$title."</title>\n\n";
		$html.= "<link rel='shortcut icon' href='".$settings['favicon']."'>\n\n";

		// Add JS Language VARS
		$html.= $settings['js_lang'];

		// Add files
		// TODO: Add these to virtual file
		if(isset($settings['files']['less']))
		{
			$html.= $settings['files']['less']."\n";
		}
		if(isset($settings['files']['css']))
		{
			$html.= $settings['files']['css']."\n";
		}
		if(isset($settings['files']['js']))
		{
			$html.= $settings['files']['js']."\n";
		}

		// Close header
		$html.= "</head>\n\n";

		// Insert body
		$html.= "<body$body_attributes>\n\n";
		$html.= "$content\n";
		$html.= "\n</body>\n";

		// Modal window
		$html.= $this->modal(array('id'=>'default'));

		// Close and return HTML
		$html.= "</html>";
		return $html;

	}

	public function __($key)
	{
		if(function_exists('__')) return __($key);
		else if(function_exists('_')) return _($key);
		else return $key;
	}

}
