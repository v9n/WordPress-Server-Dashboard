<?php
namespace AX\StatBoard\Widget;

class Cpuload implements Provider {
  function __construct() {
  }

  public function get_title() {
    return "CPU Load";
  }

  public function get_content() {
    $metrics = $this->get_metric();
    $metrics = Cache::load($this, 3600 * 5); //5 minute cache
    if (!$metrics) {
      return false;
    }
    // see https://google-developers.appspot.com/chart/interactive/docs/gallery/barchart#Data_Format for more detai of format
    $data = array(array('Duration', '% Load'));
    foreach ($metrics as $key=>$metric) {
      array_push($data, array($metric[0], $metric[1]));
    }
    $data = json_encode($data);
    echo <<<EOD
<div id="avg_load"></div>
<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable($data);

        var options = {    
          hAxis: {
            titleTextStyle: {color: 'red'},
            minValue:0,
            maxValue:100
          }
        };

        var chart = new google.visualization.BarChart(document.getElementById('avg_load'));
        chart.draw(data, options);
      }
    </script>
EOD;
  }

  /**
   * http://stackoverflow.com/questions/11987495/linux-proc-loadavg
   *
   */
  function get_metric() {
    $number_of_core = intval(`/bin/grep -c processor /proc/cpuinfo`);
    $loadAvg = `cat /proc/loadavg | /usr/bin/awk '{print $1,$2,$3}'`;
    $loadAvg = explode(' ', $loadAvg);
    if ($loadAvg <3) {
      return false;
    }
    $loadTimes = array('1 min', '5 mins', '15 mins');
    return array_map(
      function ($loadtime, $value, $number_of_core) {
        return array($loadtime, round($value * 100 / $number_of_core, 2), $value);
      },
        $loadTimes,
        $loadAvg,
        array_fill(0, 3, $number_of_core)
      );

  }

}

