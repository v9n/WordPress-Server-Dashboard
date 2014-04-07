<?php
/**
 * Adopt from https://github.com/afaqurk/linux-dash/blob/master/sh/ip.php
 *
 */
namespace AX\StatBoard\Widget;

class Ethernet implements Provider {
  function __construct() {
  }

  public function get_title() {
    return "Ethernet";
  }

  public function get_content() {
    $interfaces = $this->get_metric();
    $html = '<table class="wp-list-table widefat"><thead><tr>
      <th>Interface</th>
      <th>IP</th>
      </tr></thead><tbody>';
    foreach ($interfaces as $interface=>$ip) {
      $html .= "<tr>
        <td>{$interface}</td>
        <td>{$ip}</td>
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
    $ethernet = array();

    $output = `ip -oneline link show | /usr/bin/awk \'{print $2}\' | /bin/sed "s/://"`;
    if (!$output) { // It didn't work with "ip" , so we do it with ifconfig
      $output = shell_exec(
        'ifconfig | /bin/grep -B1 "inet addr" | /usr/bin/awk \'' .
        '{ if ( $1 == "inet" ) { print $2 }' .
        'else if ( $2 == "Link" ) { printf "%s:",$1 } }\' | /usr/bin/awk' .
        ' -F: \'{ print $1","$3 }\''
      );
    } else {
      // Loop over the interface we found out with ip to find the Ip 
      // The output looks like
      // eth0,10.0.2.15 eth1,192.168.1.111 lo,127.0.0.1 
      // we need to parse the result
      $command = "for interface in {$output}; do" .
        ' for family in inet inet6; do'.
        ' ip -oneline -family $family addr show $interface |' .
        ' grep -v fe80 | awk \'{print $2","$4}\';' .
        ' done; done';
      $output = shell_exec($command);
    }

    if (!$output) {
      return false;
    }
    
    $lines = explode("\n", $output);
    foreach ($lines as $line) {
      $line = explode(',', $line);
      if (!is_array($line) || count($line)<2) {
        continue;
      }
      $ethernet[$line[0]] = $line[1];
    }

    return $ethernet;
  }

}

