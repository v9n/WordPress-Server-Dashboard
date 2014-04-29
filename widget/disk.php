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
    $metric = Cache::load($this, 60 * 10); //Cache disk usage for 10 minutes
    $data = array(
      array('Disk', 'Space')
    );

    $disk_container = array();
    $data_partition = array(
      array('Filesystem', 'Free(GB)', 'Used(GB)')
    );    
    foreach ($metric as $disk) {
      $size = intval($disk['size']);
      if ('M' == substr($disk['size'], -1)) {
        $size = round($size / 1024, 2);
      }
      $used = intval($disk['used']);
      if ('M' == substr($disk['used'], -1)) {
        $used = round($used / 1024, 2);
      }

      if (empty($size)) {
        continue;
      }
      $data[] = array($disk['filesystem'], $size);
      $data_partition[] = array($disk['filesystem'], $size - $used, $used);
    }
    $data = json_encode($data);
    $data_partition = json_encode($data_partition);

    echo <<<EOD
      <div id="widget_disk_usage"></div>
      <div id="widget_disk_partion"></div>
      <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(function () {
        var data = google.visualization.arrayToDataTable({$data});
        var options = {
          is3D: true,
        };
        var chart = new google.visualization.PieChart(document.getElementById('widget_disk_usage'));
        chart.draw(data, options);

        var data2 = google.visualization.arrayToDataTable({$data_partition});
        var options2 = {
          isStacked: true
        };
        var chart2 = new google.visualization.ColumnChart(document.getElementById('widget_disk_partion'));
        chart2.draw(data2, options2);

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

