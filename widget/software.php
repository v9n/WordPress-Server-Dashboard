<?php
namespace AX\StatBoard\Widget;

class Software implements Provider {
  function __construct() {
  }

  public function get_title() {
    return "Installed Software";
  }

  public function get_content() {
    $cmds = $this->get_metric();
    $content = '';
    foreach ($cmds as $cmd=>$info) {
      $content .= "<p><strong>$cmd</strong>&nbsp; $info</p>";
    }
    echo $content;
  }
  
  /**
   * Return server info: OS, Kernel, Uptime, and hostname
   * @return array with 3 metric:
   *          * hostname
   *          * os
   *          * uptime
   */
  function get_metric() {
    $cmds = array();

    $package = array(
      'php'   => '-v', 
      'node'  => '-v',
      'mysql' => '-V', 
      'vim'   => '--version',
      'python' => '-V', 
      'ruby'  => '-v', 
      'java'  => '-version',
      'curl'  => '-V');
  
    foreach ($package as $cmd=>$version_query) {
      if (NULL == $cmds[$cmd] = shell_exec("which $cmd")) {
        $cmds[$cmd] = 'Not installed';
        continue;
      }
      $version = shell_exec("$cmd $version_query");
      $version = explode("\n", $version);
      if (is_array($version)) {
        $version = array_shift($version);
      }
      $cmds[$cmd] .= '<br>' . $version;
    }
    return $cmds;
  }

}

