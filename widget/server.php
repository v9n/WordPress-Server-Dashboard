<?php
namespace AX\StatBoard\Widget;
use DateTime;

class Server implements Provider {
  function __construct() {
  }

  function getSlugId() {
    return "Server";
  }

  public function get_title() {
    return "Server Info";
  }

  public function get_content() {
    $server = $this->get_metric();
    echo <<<EOD
    <strong>Hostname</strong>&nbsp;{$server['hostname']}<br />
    <strong>OS</strong> {$server['os']}<br />
    <strong>Uptime</strong> {$server['uptime']}<br />
EOD;
  }
  
  /**
   * Return server info: OS, Kernel, Uptime, and hostname
   * @return array with 3 metric:
   *          * hostname
   *          * os
   *          * uptime
   */
  function get_metric() {
    $server = array();
    $server['hostname'] = `hostname`;
    $server['os']       = `uname -sr`;

    $total_uptime_sec = `cut -d. -f1 /proc/uptime`;
    
    $now = new DateTime("now");
    $server['uptime'] = $now->diff(new DateTime("@$total_uptime_sec"))->format('%a days, %h hours, %i minutes and %s seconds');

    return $server;
  }

}

