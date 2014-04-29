<?php
namespace AX\StatBoard\Widget;

class Ram implements Provider {
  function __construct() {
  }

  public function get_title() {
    return "Ram Usage";
  }

  public function get_content() {
    $metric = $this->get_metric();
    $metric = Cache::load($metric, 3600 * 5);
    $data = array(
      array('Type', 'Used(MB)', 'Free(MB)')
    );

    foreach ($metric as $item) {
      if (empty($item)) {
        continue;
      }
      if ($item['type'] !== 'Mem' && $item['type'] !== 'Swap') {
        continue;
      }
      if ( 0 == ($item['free'] + $item['used'])) {
        continue;
      }

      $data[] = array(
        $item['type'],$item['used'], $item['free']
        );
    }
    $data   =  json_encode($data);
    echo <<<EOD
      <div id="widget_ram_usage"></div>
      <script type="text/javascript">
      google.setOnLoadCallback(function () {
        var data = google.visualization.arrayToDataTable({$data});
        var options = {
          isStacked: true
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('widget_ram_usage'));
        chart.draw(data, options);
      })        
    </script>
EOD;
  }

  function get_metric() {
    $df = `free -m | grep -E "(Mem|Swap)" | awk '{print $1, $2, $3, $4}'`;
    $df = explode("\n", $df);
    if (is_array($df) && count($df)>=2) {
      $df = array_map(function ($line) {
        if (empty($line)) {
          return;
        }
        $segment=preg_split('/\s+/', $line);

        return array(
          'type' => trim($segment[0]," :"),
          'total' => (int)$segment[1],
          'used' =>  (int)$segment[2],
          'free' =>  (int)$segment[3],
        );
      }, $df);
      return $df;
    }
    return false;
  }

}

