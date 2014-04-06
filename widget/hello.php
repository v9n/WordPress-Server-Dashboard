<?php
namespace AX\StatBoard\Widget;

class Hello implements Provider {

  public function get_title() {
    return 'Hello world';
  }

  public function get_content() {
    echo <<<'EOD'
Hey, I'm the body of widget. Thanks for bring me to the life.
      <div id="hello_piechart">
      </div>
      <script type="text/javascript">
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
      var_dump($df);
    }
  }
}
