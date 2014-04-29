<?php
namespace AX\StatBoard\Widget;
use DateTime;

class Server implements Provider {
  function __construct() {

  }
  
  public function get_title() {
    return "Server Info";
  }

  public function get_content() {
    $server = Cache::load($this, 3600 * 24);

    echo <<<EOD
    <strong>Ip Address</strong>&nbsp;{$server['ip']}<br>
    <strong>CPU</strong>&nbsp; {$server['cpu']}<br>
    <strong>Number of Core</strong>&nbsp; {$server['core']}<br>
    <strong>Ram</strong>&nbsp; {$server['ram']}<br>
    <strong>Hostname</strong>&nbsp;{$server['hostname']}<br>
    <strong>OS</strong> {$server['os']}<br>
    <strong>Uptime</strong> {$server['uptime']}<br>
EOD;
  }
  
  /**
   * Return server info: OS, Kernel, Uptime, and hostname
   */
  function get_metric() {
    $server = array();
    $server['hostname'] = `hostname`;
    $server['os']       = `uname -sr`;
    $server['core']     = `grep -c ^processor /proc/cpuinfo`;
    $total_uptime_sec = time() - `cut -d. -f1 /proc/uptime`;
    
    $now = new DateTime("now");
    $server['uptime'] = $now->diff(new DateTime("@$total_uptime_sec"))->format('%a days, %h hours, %i minutes and %s seconds');

    // Get the external ip with ifconfig.me, a website that show you ip address in plaintext
    // when sending request with curl header
    $server['ip'] = `curl ifconfig.me`;
    $server['ram'] = `free -m | grep Mem | awk '{print $2}'`;
    $server['cpu'] =`cat /proc/cpuinfo | grep "model name" | awk '{print $4,$5,$6,$7}'`;
    
    return $server;
  }

}

