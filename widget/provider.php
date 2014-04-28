<?php
namespace AX\StatBoard\Widget;

interface Provider {
  
  function get_title();
  function get_content();
  function get_metric();

}
