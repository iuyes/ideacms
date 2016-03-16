<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright		Copyright (c) 2008 - 2014, EllisLab, Inc.
 * @copyright		Copyright (c) 2014 - 2015, British Columbia Institute of Technology (http://bcit.ca/)
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Router Class
 *
 * Parses URIs and determines routing
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @author		EllisLab Dev Team
 * @category	Libraries
 * @link		http://codeigniter.com/user_guide/general/routing.html
 */
class CI_Router {

	/**
	 * Config class
	 *
	 * @var object
	 * @access public
	 */
	var $config;
	/**
	 * List of routes
	 *
	 * @var array
	 * @access public
	 */
	var $routes			= array();
	/**
	 * List of error routes
	 *
	 * @var array
	 * @access public
	 */
	var $error_routes	= array();
	/**
	 * Current class name
	 *
	 * @var string
	 * @access public
	 */
	var $class			= '';
	/**
	 * Current method name
	 *
	 * @var string
	 * @access public
	 */
	var $method			= 'index';
	/**
	 * Sub-directory that contains the requested controller class
	 *
	 * @var string
	 * @access public
	 */
	var $directory		= '';
	/**
	 * Default controller (and method if specific)
	 *
	 * @var string
	 * @access public
	 */
	var $default_controller;

	/**
	 * Constructor
	 *
	 * Runs the route mapping function.
	 */
	function __construct()
	{
		$this->config =& load_class('Config', 'core');
		$this->uri =& load_class('URI', 'core');
		log_message('debug', "Router Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Set the route mapping
	 *
	 * This function determines what should be served based on the URI request,
	 * as well as any "routes" that have been set in the routing config file.
	 *
	 * @access	private
	 * @return	void
	 */
	function _set_routing()
	{

        if (SYS_DOMAIN) {
            $_SERVER['SCRIPT_NAME'] = str_replace('/' . SYS_DOMAIN, '', $_SERVER['SCRIPT_NAME']);
            $_SERVER['REQUEST_URI'] = str_replace('/' . SYS_DOMAIN, '', $_SERVER['REQUEST_URI']);
        }
        $path_url_string = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'] : (strlen($_SERVER['REQUEST_URI']) == 1 || $_SERVER['REQUEST_URI'] == '/' . ENTRY_SCRIPT_NAME ? '' : $_SERVER['REQUEST_URI']);
        $new_url_string  = '';
        if (!isset($_SERVER['QUERY_STRING']) || empty($_SERVER['QUERY_STRING'])) {
            $router_config_file = CONFIG_DIR . 'router.ini.php';
            if (is_file($router_config_file)) {
                $router_array   = require_once $router_config_file;
                if (is_array($router_array) && !empty($router_array)) {
                    $path_url_router = str_replace(str_replace('/' . ENTRY_SCRIPT_NAME, '', $_SERVER['SCRIPT_NAME']), '', $_SERVER['REQUEST_URI']);
                    $path_url_router = str_replace('/' . ENTRY_SCRIPT_NAME, '', $path_url_router);
                    if (substr($path_url_router, 0, 1) == '/') {
                        $path_url_router = substr($path_url_router, 1);
                    }
                    if ($path_url_router) {
                        foreach ($router_array as $router_key=>$router_value) {
                            if (preg_match('#' . $router_key . '#', $path_url_router)) {
                                $new_url_string = preg_replace('#' . $router_key . '#', $router_value, $path_url_router);
                                break;
                            }
                        }
                        if (empty($new_url_string)) {
                            if (strpos($path_url_string, '.php')) {
                                show_error('系统无法识别此地址（'.$path_url_string.'）', $status_code = 404, $heading = 'IdeaCMS');
                            } else {
                                show_error('系统无法识别此地址（'.$path_url_string.'），请检查自定义URL规则文件config/router.ini.php。', $status_code = 404, $heading = 'URL规则不匹配');
                            }
                        }
                    }
                }
            }
        }
        $path_url_string  = $new_url_string ? $new_url_string : $path_url_string;
        parse_str($path_url_string, $url_info_array);
        $namespace_name   = trim((isset($url_info_array['s']) && $url_info_array['s']) ? $url_info_array['s'] : '');
        $controller_name  = trim((isset($url_info_array['c']) && $url_info_array['c']) ? $url_info_array['c'] : DEFAULT_CONTROLLER);
        $action_name      = trim((isset($url_info_array['a']) && $url_info_array['a']) ? $url_info_array['a'] : DEFAULT_ACTION);
        if ($namespace_name == 'admin' && ADMIN_NAMESPACE != 'admin') {
            show_error('Admin管理路径被重新定义，当前地址不再生效', $status_code = 404, $heading = 'Admin');
        }
        $namespace_name == ADMIN_NAMESPACE ? 'admin' : $namespace_name;
        $_GET['s']  = strtolower($namespace_name);
        $_GET['c'] = ucfirst(strtolower($controller_name));
        $_GET['a'] = strtolower($action_name);
        $_GET             = array_merge($_GET, $url_info_array);

		// Are query strings enabled in the config file?  Normally CI doesn't utilize query strings
		// since URI segments are more search-engine friendly, but they can optionally be used.
		// If this feature is enabled, we will gather the directory/class/method a little differently
		$segments = array();

        if (isset($_GET['s']))
        {
            if ($_GET['s'] == ADMIN_NAMESPACE) {
                $this->set_directory('admin');
                $segments[] = $this->fetch_directory();
            } else {
                //
                $this->set_directory(trim($this->uri->_filter_uri($_GET['s'])));
                $segments[] = $this->fetch_directory();
            }
        }

        if (isset($_GET[$this->config->item('controller_trigger')]))
        {
            $this->set_class(trim($this->uri->_filter_uri($_GET[$this->config->item('controller_trigger')])));
            $segments[] = $this->fetch_class();
        } else {
            $this->set_class('index');
        }

        if (isset($_GET[$this->config->item('function_trigger')]))
        {
            $this->set_method(trim($this->uri->_filter_uri($_GET[$this->config->item('function_trigger')])));
            $segments[] = $this->fetch_method();
        } else {
            $this->set_method('index');
        }

		// Set the default controller so we can display it in the event
		// the URI doesn't correlated to a valid controller.
		$this->default_controller = 'index';

		// Were there any query string segments?  If so, we'll validate them and bail out since we're done.
		if (count($segments) > 0)
		{
			return $this->_validate_request($segments);
		}

		// Fetch the complete URI string
		$this->uri->_fetch_uri_string();

		// Is there a URI string? If not, the default controller specified in the "routes" file will be shown.
		if ($this->uri->uri_string == '')
		{

			return $this->_set_default_controller();
		}

		// Do we need to remove the URL suffix?
		$this->uri->_remove_url_suffix();

		// Compile the segments into an array
		$this->uri->_explode_segments();

		// Parse any custom routing that may exist
		$this->_parse_routes();
		// Re-index the segment array so that it starts with 1 rather than 0
		$this->uri->_reindex_segments();
	}

	// --------------------------------------------------------------------

	/**
	 * Set the default controller
	 *
	 * @access	private
	 * @return	void
	 */
	function _set_default_controller()
	{

        $this->default_controller = 'index';
        $this->set_class($this->default_controller);
        $this->set_method('index');
        $this->_set_request(array($this->default_controller, 'index'));

		// re-index the routed segments array so it starts with 1 rather than 0
		$this->uri->_reindex_segments();

		log_message('debug', "No URI present. Default controller set.");
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Route
	 *
	 * This function takes an array of URI segments as
	 * input, and sets the current class/method
	 *
	 * @access	private
	 * @param	array
	 * @param	bool
	 * @return	void
	 */
	function _set_request($segments = array())
	{
		$segments = $this->_validate_request($segments);
		if (count($segments) == 0)
		{
			return $this->_set_default_controller();
		}

		$this->set_class($segments[0]);

		if (isset($segments[1]))
		{
			// A standard method request
			$this->set_method($segments[1]);
		}
		else
		{
			// This lets the "routed" segment array identify that the default
			// index method is being used.
			$segments[1] = 'index';
		}

		// Update our "routed" segment array to contain the segments.
		// Note: If there is no custom routing, this array will be
		// identical to $this->uri->segments
		$this->uri->rsegments = $segments;
	}

	// --------------------------------------------------------------------

	/**
	 * Validates the supplied segments.  Attempts to determine the path to
	 * the controller.
	 *
	 * @access	private
	 * @param	array
	 * @return	array
	 */
	function _validate_request($segments)
	{
		if (count($segments) == 0)
		{
			return $segments;
		}

		// Does the requested controller exist in the root folder?
		if (file_exists(FCPATH.'controllers/'.$segments[0].'.php')
            || file_exists(FCPATH.'controllers/'.ucfirst($segments[0]).'Controller.php'))
		{
			return $segments;
		}

		// Is the controller in a sub-folder?
		if (is_dir(FCPATH.'controllers/'.$segments[0]))
		{
			// Set the directory and remove it from the segment array
			$this->set_directory($segments[0]);
			$segments = array_slice($segments, 1);

			if (count($segments) > 0)
			{
//                if ()

				// Does the requested controller exist in the sub-folder?
				if ( file_exists(FCPATH.'controllers/'.$this->fetch_directory().$segments[0].'.php')
                    || file_exists(FCPATH.'controllers/'.$this->fetch_directory().ucfirst($segments[0]).'Controller.php'))
				{

				}
			}
			else
			{
				// Is the method being specified in the route?
				if (strpos($this->default_controller, '/') !== FALSE)
				{
					$x = explode('/', $this->default_controller);

					$this->set_class($x[0]);
					$this->set_method($x[1]);
				}
				else
				{
					$this->set_class($this->default_controller);
					$this->set_method('index');
				}

				// Does the default controller exist in the sub-folder?
				if ( ! file_exists(FCPATH.'controllers/'.$this->fetch_directory().$this->default_controller.'.php')

                    && ! file_exists(FCPATH.'controllers/'.$this->fetch_directory().ucfirst($this->default_controller).'Controller.php')
                )
				{
					$this->directory = '';
					return array();
				}

			}

			return $segments;
		}

        // 验证是否是应用
        $plugin = trim($segments[0], '/');
        if (is_dir(FCPATH.'plugins/'.$plugin)) {
            // 是应用
            define('APP_DIR', $plugin);
            $segments = array_slice($segments, 1);
            if (!$segments) {
                $this->set_class($this->default_controller);
                $this->set_method('index');
            } elseif (count($segments) == 1) {
                if (file_exists(FCPATH.'plugins/'.APP_DIR.'/controllers/'.ucfirst($segments[0]).'Controller.php')) {
                    $this->set_class($segments[0]);
                    $this->set_method('index');
                } else {
                    $this->set_class('index');
                    $this->set_method($segments[0]);
                }
            } else {
                $this->set_class($segments[0]);
                $this->set_method($segments[1]);
            }
            return $segments;
        }

		// If we've gotten this far it means that the URI does not correlate to a valid
		// controller class.  We will now see if there is an override
		if ( ! empty($this->routes['404_override']))
		{
			$x = explode('/', $this->routes['404_override']);

			$this->set_class($x[0]);
			$this->set_method(isset($x[1]) ? $x[1] : 'index');

			return $x;
		}


		// Nothing else to do at this point but show a 404
		show_404($segments[0]);
	}

	// --------------------------------------------------------------------

	/**
	 *  Parse Routes
	 *
	 * This function matches any routes that may exist in
	 * the config/routes.php file against the URI to
	 * determine if the class/method need to be remapped.
	 *
	 * @access	private
	 * @return	void
	 */
	function _parse_routes()
	{
		// Turn the segment array into a URI string
		$uri = implode('/', $this->uri->segments);

		// Is there a literal match?  If so we're done
		if (isset($this->routes[$uri]))
		{
			return $this->_set_request(explode('/', $this->routes[$uri]));
		}

		// Loop through the route array looking for wild-cards
		foreach ($this->routes as $key => $val)
		{
			// Convert wild-cards to RegEx
			$key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));

			// Does the RegEx match?
			if (preg_match('#^'.$key.'$#', $uri))
			{
				// Do we have a back-reference?
				if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
				{
					$val = preg_replace('#^'.$key.'$#', $val, $uri);
				}

				return $this->_set_request(explode('/', $val));
			}
		}

		// If we got this far it means we didn't encounter a
		// matching route so we'll set the site default route
		$this->_set_request($this->uri->segments);
	}

	// --------------------------------------------------------------------

	/**
	 * Set the class name
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function set_class($class)
	{
		$this->class = str_replace(array('/', '.'), '', $class);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the current class
	 *
	 * @access	public
	 * @return	string
	 */
	function fetch_class()
	{
		return $this->class;
	}

	// --------------------------------------------------------------------

	/**
	 *  Set the method name
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function set_method($method)
	{
		$this->method = $method;
	}

	// --------------------------------------------------------------------

	/**
	 *  Fetch the current method
	 *
	 * @access	public
	 * @return	string
	 */
	function fetch_method()
	{
		if ($this->method == $this->fetch_class())
		{
			return 'index';
		}

		return $this->method;
	}

	// --------------------------------------------------------------------

	/**
	 *  Set the directory name
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	function set_directory($dir)
	{
		$this->directory = str_replace(array('/', '.'), '', $dir).'/';
	}

	// --------------------------------------------------------------------

	/**
	 *  Fetch the sub-directory (if any) that contains the requested controller class
	 *
	 * @access	public
	 * @return	string
	 */
	function fetch_directory()
	{
		return $this->directory;
	}

	// --------------------------------------------------------------------

	/**
	 *  Set the controller overrides
	 *
	 * @access	public
	 * @param	array
	 * @return	null
	 */
	function _set_overrides($routing)
	{
		if ( ! is_array($routing))
		{
			return;
		}

		if (isset($routing['directory']))
		{
			$this->set_directory($routing['directory']);
		}

		if (isset($routing['controller']) AND $routing['controller'] != '')
		{
			$this->set_class($routing['controller']);
		}

		if (isset($routing['function']))
		{
			$routing['function'] = ($routing['function'] == '') ? 'index' : $routing['function'];
			$this->set_method($routing['function']);
		}
	}


}
// END Router Class