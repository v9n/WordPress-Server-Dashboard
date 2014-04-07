<?php
/**
 * Adopt from https://github.com/afaqurk/linux-dash/blob/master/sh/ip.php
 *
 */
namespace AX\StatBoard\Widget;

class Diskio implements Provider {
  function __construct() {
  }

  public function get_title() {
    return "Disk IO";
  }

  public function get_content() {
    $interfaces = $this->get_metric();
    //$html = '<table class="wp-list-table widefat"><thead><tr>
      //<th>Interface</th>
      //<th>IO rate</th>
      //</tr></thead><tbody>';
    //foreach ($interfaces as $interface=>$ip) {
      //$id = md5($interface);
      //$html .= "<tr>
        //<td>{$interface}</td>
        //<td>
        //<div id='nio_{$id}'></div>
      //<script type=\"text/javascript\">
      //google.setOnLoadCallback(function () {
        //var data = google.visualization.arrayToDataTable({$data});

        //var options = {
        //};

        //var chart = new google.visualization.BarChart(document.getElementById('{$id}'));
        //chart.draw(data, options);
      //})        
    //</script>
        
        //</td>
        //</tr>";
    //}
    //$html .= '</tbody></table>




      //';
    //echo $html;
    $data = array_merge(array(array('Interface', 'Receive', 'Transfer')), $interfaces);
    $data = json_encode($data); 
    echo <<<EOD
      <div id="nio_chart"></div>
      <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(function () {
        var data = google.visualization.arrayToDataTable({$data});

        var options = {
        };

        var chart = new google.visualization.BarChart(document.getElementById('nio_chart'));
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
      //Convert Bytes to KB
      //We use this format to feed the google barchart
      //https://developers.google.com/chart/interactive/docs/gallery/barchart#StackedBars
      $line[1] = round((int)$line[1] / 1024);
      $line[2] = round((int)$line[2] / 1024);
      $ethernet[] = $line;
      //$ethernet[$line[0]] = array('rx' => round($line[1]/1024/1024)
        //, 'tx' => round($line[2]/1024/1024));
    }

    return $ethernet;
  }

}

