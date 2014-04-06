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

require_once plugin_dir_path( __FILE__ ) . '/widget.php' ;
require_once plugin_dir_path( __FILE__ ) . '/widget/provider.php' ;

class Dashboard {
  protected static $_instance=NULL;
  protected $_dashboard_widget = array();

  function __construct() {
    $this->_dashboard_widget = array(
      'server',
      //'hello',
      'disk',
      'ram',
  
  //const DASHBOARD_WIDGET_SERVER_LOAD    = 20;

  //const DASHBOARD_WIDGET_MEMORY         = 30;

  //const DASHBOARD_WIDGET_DISK_IO        = 41;
  
  //const DASHBOARD_WIDGET_SOFTWARE       = 50;
  
  //const DASHBOARD_WIDGET_SERVER_IP      = 60;

  //const DASHBOARD_WIDGET_INTERNET_SPEED = 70;
  //const DASHBOARD_WIDGET_NETWORK_IO     = 71;
  
  //const DASHBOARD_WIDGET_PROCESS        = 80;
    );
    foreach ($this->_dashboard_widget as $item) {
      if (!file_exists(plugin_dir_path( __FILE__ ) . '/widget/' . $item . '.php')) {
        continue;
      }
      require_once plugin_dir_path( __FILE__ ) . '/widget/' . $item . '.php' ;
      $classname = 'AX\\StatBoard\\Widget\\' . ucwords($item);
      //$class = new \ReflectionClass('ReflectionClass');
      //$p = $class->newInstance($classname);
      $p = new $classname();
      Widget::instance()->add_provider($item, $p);
      //Widget::instance()->add_provider($item, new Hello());
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
    add_action('wp_footer', array($this, 'footer'));
  }

  function remove_dashboard_widgets() {

  }

  function add_dashboard_widgets() {
    syslog(LOG_DEBUG, "Run"); 
    $widget = Widget::instance();
    foreach ($widget->get_provider() as $name=>$provider) {
      $widget->register($name);
    }
  }

  /**
   * Add java script
   */ 
  function add_asset() {
    //wp_enqueue_style( 'style-name', get_stylesheet_uri() );
    syslog(LOG_DEBUG, "Loaded"); 
    wp_enqueue_script( 'google-chart', 'https://www.google.com/jsapi' );
    wp_enqueue_script( 'plugin_dir_url', plugin_dir_url(__FILE__) . '/loader.js');
  }

  function footer() {
    echo '
      <script>google.load("visualization", "1", {packages:["corechart"]})</script>
      ';
  }
}

Dashboard::instance()->run();
