<?php
/*
Plugin Name: Server Dashboard
Version: 0.1-alpha
Description: Server Status Dashboard
Author: Vinh
Author URI: http://axcoto.com
Plugin URI: http://axcoto.com
Text Domain: Server Dashboard
Domain Path: /languages
 */
namespace AX\StatBoard;

use AX\StatBoard\Widget;

class Dashboard {
  protected static $_instance=NULL;
  protected $_dashboard_widget = array();
  protected $_plugin_dir=NULL;

  /**
   * Auto load class under namespace of this plugin
   */
  public function load_class($classname)
  {
    if (FALSE === $pos = strpos($classname, 'AX\StatBoard')) {
      return false;
    }
    $classname = substr($classname, $pos+1 + strlen('AX\StatBoard'));
    $filepath = $this->_plugin_dir . strtolower(str_replace('\\', '/', $classname) . '.php');
    if (!file_exists($filepath)) {
      return false;
    }
    include $filepath;
  } 
  
  /**
   * Setup variable and intialize widget provider
   */
  function __construct() {
    $this->_plugin_dir =  plugin_dir_path( __FILE__ ) ;
    spl_autoload_register(array($this, 'load_class'));

    $this->_dashboard_widget = array(
      'server',
      //'cpu_load',

      'ram',
      
      'disk',
      'diskio',

      'software',
      'ethernet',
      
      'internetspeed',
      'networkio',
      'process',
    );

    foreach ($this->_dashboard_widget as $item) {
      if (!file_exists($this->_plugin_dir . '/widget/' . $item . '.php')) {
        continue;
      }
      $classname = 'AX\\StatBoard\\Widget\\' . ucwords($item);
      Widget::instance()->add_provider($item, new $classname());
    }
  }

  /**
   * Create an unique instance throught the app
   */
  public static function instance() {
    return self::$_instance = self::$_instance ?: new self();
  }

  /**
   * Start to setup hook
   */
  public function run() {
    add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
    add_action( 'admin_enqueue_scripts', array($this, 'add_asset'));
    add_action( 'wp_footer', array($this, 'footer'));
  }
  
  /**
   * Register dashboard widget proider to show up on dashboard
   */
  function add_dashboard_widgets() {
    $widget = Widget::instance();
    foreach ($widget->get_provider() as $name=>$provider) {
      $widget->register($name);
    }
  }

  /**
   * Assets load: stylesheet, JS. 
   */ 
  function add_asset() {
    syslog(LOG_DEBUG, "Loaded"); 
    wp_enqueue_script( 'google-chart', 'https://www.google.com/jsapi' );
    //wp_enqueue_script( 'plugin_dir_url', plugin_dir_url(__FILE__) . '/loader.js');
  }

  /**
   * Inline JavaScript for chart
   */
  function footer() {
    echo '
      <script>google.load("visualization", "1", {packages:["corechart"]})</script>
';
  }
}

Dashboard::instance()->run();
