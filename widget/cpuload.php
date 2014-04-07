<?php
namespace AX\StatBoard\Widget;

class Cpuload implements Provider {
  function __construct() {
  }

  public function get_title() {
    return "CPU Load";
  }

  public function get_content() {
    $metric = $this->get_metric();
    $data = array(
    );

    foreach ($metric as $item) {
      if ($item['type'] != 'Mem' && $item['type'] != 'Swap') {
        continue;
      }
      if ( 0 == ($item['free'] + $item['used'])) {
        continue;
      }

      //$data[$item['type']][] = array('Type', 'Space');
      $data[$item['type']] = array(
        array('Type', 'Space'),
        array('Free', intval($item['free'])),
        array('Used', intval($item['used'])),
      );

      $data[$item['type']]   =  json_encode($data[$item['type']]);
    }
    //$data['Mem'] = json_encode($data['Mem']);
    //$data['Swap'] = json_encode($data['Swap']);
    echo <<<EOD
      <div id="widget_ram_usage"></div>
      <div id="widget_swap_usage"></div>
      <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(function () {
        var data = google.visualization.arrayToDataTable({$data['Mem']});

        var options = {
          is3D: true,
        };

        var chart = new google.visualization.PieChart(document.getElementById('widget_ram_usage'));
        chart.draw(data, options);
      })        
    </script>
EOD;
  }
  
  /**
   * http://stackoverflow.com/questions/11987495/linux-proc-loadavg
   *
   */
  function get_metric() {
    ('/bin/grep -c ^processor /proc/cpuinfo', $resultNumberOfCores);
    $numberOfCores = $resultNumberOfCores[0];
  
    exec(
      '/bin/cat /proc/loadavg | /usr/bin/awk \'{print $1","$2","$3}\'',
      $resultLoadAvg
    );
    $loadAvg = explode(',', $resultLoadAvg[0]);

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(
      array_map(
        function ($value, $numberOfCores) {
          return array($value, (int)($value * 100 / $numberOfCores));
        },
          $loadAvg,
          array_fill(0, count($loadAvg), $numberOfCores)
        )
      );

  }

}

