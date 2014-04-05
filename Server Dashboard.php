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

use Ax\StatBoard\Widget;

require_once plugin_dir_path( __FILE__ ) . '/widget.php' ;

class Dashboard {
  protected static $_instance=NULL;

  function __construct() {
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
  }

  function remove_dashboard_widgets() {

  }

  function add_dashboard_widgets() {
    syslog(LOG_DEBUG, "Run"); 

    wp_add_dashboard_widget(
        'hello_world_dashboard_widget', // A Slug to identify this widget
                 'Hello World', //Widget title
                 function () {
                   echo <<<'EOD'
Hey, I'm the body of widget. Thanks for bring me to the life.
      <div id="hello_piechart">
      </div>
      <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Task', 'Hours per Day'],
          ['Work',     11],
          ['Eat',      2],
          ['Commute',  2],
          ['Watch TV', 2],
          ['Sleep',    7]
        ]);

        var options = {
          title: 'Sample Pie Chart',
          is3D: true,
        };

        var chart = new google.visualization.PieChart(document.getElementById('hello_piechart'));
        chart.draw(data, options);
      }
    </script>
EOD;
                  } //function to render content of widget, I'm using a closure here
      );	

    }

  /**
   * Add java script
   */ 
  function add_asset() {
    //wp_enqueue_style( 'style-name', get_stylesheet_uri() );
    syslog(LOG_DEBUG, "Loaded"); 
    wp_enqueue_script( 'google-chart', 'https://www.google.com/jsapi' );


    //$df = `df -h`;
    //$df = explode("\n", $df);
    //if (is_array($df) && count($df)>=2) {
      //array_shift($df); //Get rid the first line
      //$df = array_map(function ($line) {
        //if (empty($line)) {
          //return NULL;
        //}
        //$segment=preg_split('/\s+/', $line);
        
        //return array(
          //'filesystem' => $segment[0],
          //'size' => $segment[1],
          //'used' => $segment[2],
          //'available' => $segment[3],
          //'use_percent' => $segment[4],
        //);
      //}, $df);
      //var_dump($df);
    //}


  }


}

Dashboard::instance()->run();
