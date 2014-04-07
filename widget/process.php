<?php
namespace AX\StatBoard\Widget;
use DateTime;

class Process implements Provider {
  function __construct() {
  }

  public function get_title() {
    return "Processes";
  }

  public function get_content() {
    $processes = $this->get_metric();
    $html = '<table class="wp-list-table widefat"><thead><tr>
      <th>User</th>
      <th>Pid</th>
      <th>%CPU</th>
      <th>%Mem</th>
      <th>Command</th>
      </tr></thead><tbody>';
    foreach ($processes as $process) {
      $html .= "<tr>
        <td>{$process['user']}</td>
        <td>{$process['pid']}</td>
        <td>{$process['%cpu']}</td>
        <td>{$process['%mem']}</td>
        <td>{$process['command']}</td>
        </tr>";
    }
    $html .= '</tbody></table>';
    echo $html;
  }
  
  /**
   * Return server info: OS, Kernel, Uptime, and hostname
   * @return array with 3 metric:
   *          * hostname
   *          * os
   *          * uptime
   */
  function get_metric() {
    $processes = array();
    $output = `ps aux | awk '{ print $1,$2,$3,$4,$9,$10,$11 }'`;
    $output = explode("\n", $output);
    if (!is_array($output) || count($output)<2) {
      return false;      
    }
    array_shift($output);
    foreach ($output as $line) {
      //$line = preg_split('/\s+/', $line);
      $line = explode(' ', $line);
      if (count($line)<6) {
        continue;
      }
      //var_dump($line);
      //echo count($line);
      $processes[] = array_combine(array('user', 'pid', '%cpu', '%mem','start','time', 'command'), $line);
    }

    return $processes;
  }

}

