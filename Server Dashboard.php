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
class Widget {
    protected $_instance=NULL;

    function __construct() {
        add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
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
    public function init() {
    }
 
    function remove_dashboard_widgets() {
 
    }
 
    function add_dashboard_widgets() {
 
    }
 
}
 
$wdw = new Widget();
