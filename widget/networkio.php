<?php
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
      google.setOnLoadCallback(function () {
        var data = google.visualization.arrayToDataTable({$data});

        var options = {
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('nio_chart'));
        chart.draw(data, options);
      })        
    </script>
EOD;

  }


  function get_metric() {
    $ethernet = array();
    
    $output = `netstat -i | grep -v -E '(Iface|Interface)' | awk '{print $1","$4","$8}'`;

    $lines = explode("\n", $output);
    foreach ($lines as $line) {
      $line = explode(',', $line);
      if (count($line)<3) {
        continue;
      }
      $ethernet[] = array($line[0], intval($line[1]), intval($line[2]));
    }
    return $ethernet;
  }

}

