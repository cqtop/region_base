<?php
(defined('BASEPATH')) or exit('No direct script access allowed');

/* load the HMVC_Loader class */


class REST_Loader extends CI_Loader {
    protected $_ci_modules = array();

    /**
     * List of loaded controllers
     *
     * @var array
     * @access protected
     */
    protected $_ci_controllers = array();

    /**
     * Constructor
     *
     * Add the current module to all paths permanently
     */
    public function __construct() {
        parent::__construct();

        $this->add_package_path(dirname(__FILE__) . '/../');
        $this->helper('api');
        // Get current module from the router
        $router = & $this->_ci_get_component('router');

        if ($router->module) {
            $this->add_module($router->module);
        }

    }

    /**
     * Controller Loader
     *
     * This function lets users load and hierarchical controllers to enable HMVC support
     *
     * @param	string	the uri to the controller
     * @param	array	parameters for the requested method
     * @param	boolean return the result instead of showing it
     * @return	void
     */
    public function controller($uri, $params = array(), $return = FALSE) {
        // No valid module detected, add current module to uri
        list($module) = $this->detect_module($uri);
        if (!isset($module)) {
            $router = & $this->_ci_get_component('router');
            if ($router->module) {
                $module = $router->module;
                $uri = $module . '/' . $uri;
            }
        }

        // Add module
        $this->add_module($module);

        // Execute the controller method and capture output
        $void = $this->_load_controller($uri, $params, $return);

        // Remove module
        $this->remove_module();

        return $void;
    }

    /**
     * Class Loader
     *
     * This function lets users load and instantiate classes.
     * It is designed to be called from a user's app controllers.
     *
     * @param	string	the name of the class
     * @param	mixed	the optional parameters
     * @param	string	an optional object name
     * @return	void
     */
    public function library($library = '', $params = NULL, $object_name = NULL) {
        if (is_array($library)) {
            foreach ($library as $class) {
                $this->library($class, $params);
            }
            return;
        }

        // Detect module
        if (list($module, $class) = $this->detect_module($library)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::library($class, $params, $object_name);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::library($class, $params, $object_name);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            return parent::library($library, $params, $object_name);
        }
    }

    /**
     * Model Loader
     *
     * This function lets users load and instantiate models.
     *
     * @param	string	the name of the class
     * @param	string	name for the model
     * @param	bool	database connection
     * @return	void
     */
    public function model($model, $name = '', $db_conn = FALSE) {
        if (is_array($model)) {
            foreach ($model as $babe) {
                $this->model($babe);
            }
            return;
        }

        // Detect module
        if (list($module, $class) = $this->detect_module($model)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::model($class, $name, $db_conn);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::model($class, $name, $db_conn);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            return parent::model($model, $name, $db_conn);
        }
    }

    /**
     * Load View
     *
     * This function is used to load a "view" file.  It has three parameters:
     *
     * 1. The name of the "view" file to be included.
     * 2. An associative array of data to be extracted for use in the view.
     * 3. TRUE/FALSE - whether to return the data or load it.  In
     * some cases it's advantageous to be able to return data so that
     * a developer can process it in some way.
     *
     * @param	string
     * @param	array
     * @param	bool
     * @return	void
     */
    public function view($view, $vars = array(), $return = FALSE) {
        // Detect module
        if (list($module, $class) = $this->detect_module($view)) {
            // Module already loaded

            if (in_array($module, $this->_ci_modules)) {
                return parent::view($class, $vars, $return);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::view($class, $vars, $return);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            return parent::view($view, $vars, $return);
        }
    }

    /**
     * Loads a config file
     *
     * @param	string
     * @param	bool
     * @param 	bool
     * @return	void
     */
    public function config($file = '', $use_sections = FALSE, $fail_gracefully = FALSE) {
        // Detect module
        if (list($module, $class) = $this->detect_module($file)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::config($class, $use_sections, $fail_gracefully);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::config($class, $use_sections, $fail_gracefully);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            parent::config($file, $use_sections, $fail_gracefully);
        }
    }

    /**
     * Load Helper
     *
     * This function loads the specified helper file.
     *
     * @param	mixed
     * @return	void
     */
    public function helper($helper = array()) {
        if (is_array($helper)) {
            foreach ($helper as $help) {
                $this->helper($help);
            }
            return;
        }

        // Detect module
        if (list($module, $class) = $this->detect_module($helper)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::helper($class);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::helper($class);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            return parent::helper($helper);
        }
    }

    /**
     * Loads a language file
     *
     * @param	array
     * @param	string
     * @return	void
     */
    public function language($file = array(), $lang = '') {
        if (is_array($file)) {
            foreach ($file as $langfile) {
                $this->language($langfile, $lang);
            }
            return;
        }

        // Detect module
        if (list($module, $class) = $this->detect_module($file)) {
            // Module already loaded
            if (in_array($module, $this->_ci_modules)) {
                return parent::language($class, $lang);
            }

            // Add module
            $this->add_module($module);

            // Let parent do the heavy work
            $void = parent::language($class, $lang);

            // Remove module
            $this->remove_module();

            return $void;
        } else {
            return parent::language($file, $lang);
        }
    }

    /**
     * Add Module
     *
     * Allow resources to be loaded from this module path
     *
     * @param	string
     * @param 	boolean
     */
    public function add_module($module, $view_cascade = TRUE) {
        if ($path = $this->find_module($module)) {
            // Mark module as loaded
            array_unshift($this->_ci_modules, $module);

            // Add package path
            parent::add_package_path($path, $view_cascade);
        }
    }

    /**
     * Remove Module
     *
     * Remove a module from the allowed module paths
     *
     * @param	type
     * @param 	bool
     */
    public function remove_module($module = '', $remove_config = TRUE) {
        if ($module == '') {
            // Mark module as not loaded
            array_shift($this->_ci_modules);

            // Remove package path
            parent::remove_package_path('', $remove_config);
        } else if (($key = array_search($module, $this->_ci_modules)) !== FALSE) {
            if ($path = $this->find_module($module)) {
                // Mark module as not loaded
                unset($this->_ci_modules[$key]);

                // Remove package path
                parent::remove_package_path($path, $remove_config);
            }
        }
    }

    /**
     * Controller loader
     *
     * This function is used to load and instantiate controllers
     *
     * @param	string
     * @param	array
     * @param	boolean
     * @return	object
     */
    private function _load_controller($uri = '', $params = array(), $return = FALSE) {
        $router = & $this->_ci_get_component('router');

        // Back up current router values (before loading new controller)
        $backup = array();
        foreach (array('directory', 'class', 'method', 'module') as $prop) {
            $backup[$prop] = $router->{$prop};
        }

        // Locate the controller
        $segments = $router->locate(explode('/', $uri));
        $class = isset($segments[0]) ? $segments[0] : FALSE;
        $method = isset($segments[1]) ? $segments[1] : "index";

        // Controller not found
        if (!$class) {
            return;
        }

        if (!array_key_exists(strtolower($class), $this->_ci_controllers)) {
            // Determine filepath
            $filepath = APPPATH . 'controllers/' . $router->fetch_directory() . $class . '.php';

            // Load the controller file
            if (file_exists($filepath)) {
                include_once ($filepath);
            }

            // Controller class not found, show 404
            if (!class_exists($class)) {
                show_404("{$class}/{$method}");
            }

            // Create a controller object
            $this->_ci_controllers[strtolower($class)] = new $class();
        }

        $controller = $this->_ci_controllers[strtolower($class)];

        // Method does not exists
        if (!method_exists($controller, $method)) {
            show_404("{$class}/{$method}");
        }

        // Restore router state
        foreach ($backup as $prop => $value) {
            $router->{$prop} = $value;
        }

        // Capture output and return
        ob_start();
        $result = call_user_func_array(array($controller, $method), $params);

        // Return the buffered output
        if ($return === TRUE) {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }

        // Close buffer and flush output to screen
        ob_end_flush();

        // Return controller return value
        return $result;
    }

    /**
     * Detects the module from a string. Returns the module name and class if found.
     *
     * @param	string
     * @return	array|boolean
     */
    private function detect_module($class) {
        $class = str_replace('.php', '', trim($class, '/'));
        if (($first_slash = strpos($class, '/')) !== FALSE) {
            $module = substr($class, 0, $first_slash);
            $class = substr($class, $first_slash + 1);

            // Check if module exists
            if ($this->find_module($module)) {
                return array($module, $class);
            }
        }

        return FALSE;
    }

    /**
     * Searches a given module name. Returns the path if found, FALSE otherwise
     *
     * @param string $module
     * @return string|boolean
     */
    private function find_module($module) {
        $config = & $this->_ci_get_component('config');

        // Check all locations for this module
        foreach ($config->item('modules_locations') as $location) {
            $path = APPPATH.$location . rtrim($module, '/') . '/';
            if (is_dir($path)) {
                return $path;
            }
        }

        return FALSE;
    }
}