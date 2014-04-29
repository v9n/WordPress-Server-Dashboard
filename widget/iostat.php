<?php
namespace AX\StatBoard\Widget;

class Iostat implements Provider {
  function __construct() {
  }

  public function get_title() {
    return "Disk IO";
  }

  public function get_content() {
    $metric = $this->get_metric();
    $metric = Cache::load($this, 300); //5 minute cache for disk io

    $disk_io = array(
      array('Disk', 'Read(MB)', 'Write(MB)'),
    );
    foreach ($metric['disk'] as $disk=>$stat) {
      $disk_io[] = array($disk, $stat['read'], $stat['write']);
    }
    $disk_io = json_encode($disk_io);   
  
    $cpu_io = json_encode(array(
      array('CPU Time', 'Percent'),
      array('IO Wait', $metric['cpu']['io_wait']),
    ));
    
    echo <<<EOD
      <div id="widget_disk_io"></div>
      <div id="widget_cpu_io_wait"></div>
      <script type="text/javascript">
      google.load('visualization', '1', {packages:['gauge']});
      google.setOnLoadCallback(function () {
        var data = google.visualization.arrayToDataTable({$cpu_io});
        var goptions = {
          redFrom: 90, redTo: 100,
          yellowFrom:75, yellowTo: 90,
          minorTicks: 5
        };
        var chart = new google.visualization.Gauge(document.getElementById('widget_cpu_io_wait'));
        chart.draw(data, goptions);

        var data2 = google.visualization.arrayToDataTable({$disk_io});
        var chart2 = new google.visualization.ColumnChart(document.getElementById('widget_disk_io'));
        chart2.draw(data2, {});
      })        
    </script>
EOD;

  }

  /**
   * Make sure we install package sysstat
   * yum install sysstat
   * or apt-get install sysstat
   *
   * Return IO Stat information. CPU waiting time, disk read/write
   *
   */
  function get_metric() {
    $metric = array();


    //Sample return:
    //Linux 2.6.32-358.23.2.el6.x86_64 (vagrant-centos64.vagrantup.com) 	04/10/2014 	_x86_64_	(1 CPU)

    //avg-cpu:  %user   %nice %system %iowait  %steal   %idle
               //0.99    0.00    4.50    1.06    0.00   93.45

    //Device:            tps   Blk_read/s   Blk_wrtn/s   Blk_read   Blk_wrtn
    //sda              13.01      3033.87         9.16   62978242     190192
    $output = `iostat`;
    $number_of_core = intval(`/bin/grep -c processor /proc/cpuinfo`);

    $lines = explode("\n", $output);
    //We should have more than  4 lines
    if (!is_array($lines) || sizeof($lines)<4) {
      return false;
    }
    $avg_cpu = preg_split("/\s+/", $lines[3]);
    $metric['cpu'] = array(
      'user'    => floatval($avg_cpu[0]) * $number_of_core,
      'system'  => floatval($avg_cpu[2]) * $number_of_core,
      'io_wait' => floatval($avg_cpu[3]) * $number_of_core,
      'other'   => 100 - ($avg_cpu[0] + $avg_cpu[2] + $avg_cpu[3])
    );
    
    if (sizeof($lines) >=7) {
      for ($i=6,$l = sizeof($lines);$i<$l; $i++) {
        $line = preg_split("/\s+/", $lines[$i]);
        if (!is_array($line) || sizeof($line)<5) {
          continue;
        }
        // Calculate block size
        $block_size = shell_exec("cat /sys/block/{$line[0]}/queue/physical_block_size");

        $metric['disk'][$line[0]] = array(
          'read'  => floatval($line[2]) * $block_size / 1024,
          'write' => floatval($line[3]) * $block_size / 1024,
        );

      }  
    }

    return $metric;
  }

}

