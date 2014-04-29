<?php
namespace AX\StatBoard\Widget;

class Ethernet implements Provider {
  function __construct() {
  }

  public function get_title() {
    return "Ethernet";
  }

  public function get_content() {
    $interfaces = Cache::load($this, 3600 * 24);

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

  function get_metric() {
    $output = shell_exec("ip -oneline link show | awk '{print $2}' | sed 's/://'");
    if (!$output) { // It didn't work with "ip" , so we do it with ifconfig
      $output = `ifconfig | grep "Link encap" | awk '{ print $1 }'`;
      $interfaces = explode("\n", $output);
      $output = `ifconfig | grep "inet addr" | awk '{ print $2 }' | sed 's/addr://'`;
      $addreses = explode("\n", $output);
      $output = trim($output, " \n");
      return array_combine($interfaces, $addreses);
    }
    // Loop over the interface we found out with ip to find the Ip 
    // The output looks like
    // eth0,10.0.2.15 eth1,192.168.1.111 lo,127.0.0.1 
    // we need to parse the result
    $output = trim($output, " \n");
    $interfaces = explode("\n", $output);
    $addreses = array();
    foreach ($interfaces as $interface) {
      $output = shell_exec("ip -oneline -family inet addr show $interface | awk '{print $4}' | cut -d'/' -f1");
      $addreses[] = $output;
    }
    
    return array_combine($interfaces, $addreses);
  }

}

