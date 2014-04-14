<?php
/**
 * Adopt from https://github.com/afaqurk/linux-dash/blob/master/sh/ip.php
 *
 */
namespace AX\StatBoard\Widget;

class Networkio implements Provider {
  function __construct() {
  }

  public function get_title() {
    return "Network IO";
  }

  public function get_content() {
    $interfaces = $this->get_metric();
    $data = array_merge(array(array('Interface', 'Receive(package)', 'Transfer(package)')), $interfaces);
    $data = json_encode($data); 
    echo <<<EOD
      <div id="nio_chart"></div>
      <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(function () {
        var data = google.visualization.arrayToDataTable({$data});

        var options = {
          //width: 0,
          isStacked: true
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('nio_chart'));
        chart.draw(data, options);
      })        
    </script>
EOD;

  }

  /**
   * Return network IO for each of interface
   * @return array with 3 metric:
   *          * hostname
   *          * os
   *          * uptime
   */
  function get_metric() {
    $ethernet = array();
    
    $output = `netstat -i | awk '{print $1","$4","$8}'`;

    $lines = explode("\n", $output);
    foreach ($lines as $line) {
      $line = explode(',', $line);
      if (!is_array($line) || count($line)<3) {
        continue;
      }
      //Ignore loopback interface, we don't need it.
      if (in_array($line[0], array('Kernel', 'Iface', 'lo'))) {
        continue;
      }
      //RX packages
      $line[1] = intval($line[1]);
      //TX packages
      $line[2] = intval($line[2]);
      $ethernet[] = $line;
    }
    return $ethernet;
  }

}

