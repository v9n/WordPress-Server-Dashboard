<?php
namespace AX\StatBoard\Widget;

class Ram implements Provider {
  function __construct() {
  }

  function getSlugId() {
    return "Ram";
  }

  public function get_title() {
    return "Ram Usage";
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

  function get_metric() {
    $df = `free -m`;
    $df = explode("\n", $df);
    if (is_array($df) && count($df)>=2) {
      array_shift($df); //Get rid the first line
      
      $df = array_map(function ($line) {
        if (empty($line)) {
          return NULL;
        }
        $segment=preg_split('/\s+/', $line);

        return array(
          'type' => trim($segment[0]," :"),
          'total' => $segment[1],
          'used' => $segment[2],
          'free' => $segment[3],
        );
      }, $df);
      return $df;
    }
    return false;
  }

}

