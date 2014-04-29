<?php
/*
Plugin Name: Server Dashboard
Version: 1.0
Description: Server Status Dashboard
Author: Vinh
Author URI: http://axcoto.com
Plugin URI: http://axcoto.com
Text Domain: Server Dashboard
Domain Path: /languages
 */
namespace AX\StatBoard;

use AX\StatBoard\Widget;
use AX\StatBoard\Widget\Cache;

class Dashboard {
  protected static $_instance=NULL;
  protected $_dashboard_widget = array();
  protected $_plugin_dir=NULL;
  const CAP_METRIC = 'server_metric';

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
      'cpuload',
      'ram',
      'disk',
      'software',
      'process',
      'ethernet',
      'networkio',
      
      'iostat',
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
    add_action( 'admin_footer', array($this, 'footer'));

    register_activation_hook(__FILE__, array($this, 'add_servermetric_caps'));
    register_deactivation_hook(__FILE__, array($this, 'remove_servermetric_caps'));

    add_filter( 'cron_schedules', array($this, 'cron_3min') ); 
    add_action( 'metric_generate_every_3min', array($this, 'generate_metric') );
    add_action( 'init', array($this, 'setup_schedule') );

  }
  
  /**
   * Register dashboard widget proider to show up on dashboard
   */
  function add_dashboard_widgets() {
    if (!current_user_can(self::CAP_METRIC)) {
      return false;  
    }
    $widget = Widget::instance();
    foreach ($widget->get_provider() as $name=>$provider) {      
      $widget->register($name);
    }
  }

  /**
   * Assets load: stylesheet, JS. 
   */ 
  function add_asset() {
    wp_enqueue_script( 'google-chart', 'https://www.google.com/jsapi' );
  }

  /**
   * Inline JavaScript for chart
   */
  function footer() {
    echo '
      <script>google.load("visualization", "1", {packages:["corechart"]})</script>
';
  }
  
  /**
   * Add severmetric capability for admin by default
   */
  function add_servermetric_caps() {
    // gets the author role
    $role = get_role( 'administrator' );
    // This only works, because it accesses the class instance.
    // would allow the author to edit others' posts for current theme only
    $role->add_cap( self::CAP_METRIC ); 
  }

  function remove_servermetric_caps() {
    // get_role returns an instance of WP_Role.
	  $role = get_role( 'administrator' );
  	$role->remove_cap( self::CAP_METRIC );
  }


  /**
   * Define a new kind of interval
   * https://codex.wordpress.org/Function_Reference/wp_get_schedules
   */
  function cron_3min($schedules) {
    $schedules['3min'] = array(
        'interval' => 3 * 60,
        'display' => __( 'Once every 3 minutes' )
    );
    return $schedules;
  }

  /**
   * Setup schedule for event. If the schedule isn't exist, 
   * we register it to 
   */
  function setup_schedule() {
    if ( ! wp_next_scheduled( 'metric_generate_every_3min' ) ) {
		  wp_schedule_event( time(), '3min', 'metric_generate_every_3min');
	  }

  }

  /**
   * The main function that runs on cron and
   * generate data
   */
  function generate_metric() {
    $widget = Widget::instance();
    file_put_contents('/tmp/d', 'Run at ' . time() . "\n\n", FILE_APPEND);
    foreach ($widget->get_provider() as $name=>$provider) {      
      //By calling get_content, we trigger Cache::load process.
      $provider->get_content();
    }
    file_put_contents('/tmp/d', 'Finished at ' . time() . "\n\n", FILE_APPEND);
  }

}

Dashboard::instance()->run();
