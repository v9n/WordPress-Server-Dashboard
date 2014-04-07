<?php
namespace AX\StatBoard\Widget;

class Disk implements Provider {
  function __construct() {
  }
  public function get_title() {
    return "Disk Usage";
  }

  public function get_content() {
    $metric = $this->get_metric();
    $data = array(
      array('Disk', 'Space')
    );

    $disk_block = array();
    $disk_container = array();
    foreach ($metric as $disk) {
      $size = intval($disk['size']);
      if (empty($size)) {
        continue;
      }
      $data[] = array($disk['filesystem'], $size);
      $disk_container[] = '';

    }
    $disk_block = '';
    $data = json_encode($data);
    echo <<<EOD
      <div id="widget_disk_usage"></div>
      {$disk_block}
      <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(function () {
        var data = google.visualization.arrayToDataTable({$data});

        var options = {
          is3D: true,
        };

        var chart = new google.visualization.PieChart(document.getElementById('widget_disk_usage'));
        chart.draw(data, options);
      })        
    </script>
EOD;
  }

  function get_metric() {
    $df = `df -h`;
    $df = explode("\n", $df);
    if (is_array($df) && count($df)>=2) {
      array_shift($df); //Get rid the first line
      $df = array_map(function ($line) {
        if (empty($line)) {
          return NULL;
        }
        $segment=preg_split('/\s+/', $line);

        return array(
          'filesystem' => $segment[0],
          'size' => $segment[1],
          'used' => $segment[2],
          'available' => $segment[3],
          'use_percent' => $segment[4],
        );
      }, $df);
      return $df;
    }
    return false;
  }

}

