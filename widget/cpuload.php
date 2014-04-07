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
    if (!$metrics) {
      return false;
    }
    $html = '<table class="wp-list-table widefat"><thead><tr>
      <th>1 min</th>
      <th>5 min</th>
      <th>15 min</th>
      </tr></thead><tbody><tr>
';
    foreach ($metrics as $metric) {
      $html .= '<td>';
      $html .= "{$metric[0]}%<br>{$metric[1]}";
      $html .= '</td>';

    }
    $html .= '</tr></tbody></table>';
    echo $html;
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

    return array_map(
      function ($value, $number_of_core) {
        return array($value, (int)($value * 100 / $number_of_core));
      },
        $loadAvg,
        array_fill(0, count($loadAvg), $number_of_core)
      );

  }

}

