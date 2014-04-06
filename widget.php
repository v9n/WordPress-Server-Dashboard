<?php
namespace AX\StatBoard;

class Widget {
  const WIDGET_SLUG_PREFIX = 'AX';

  protected $_providers = array();
  protected static $_instance;

  static function instance() {
    return self::$_instance = self::$_instance ?: new self();
  }

  function __construct() {
  }

  /**
   * Add a widget provider
   * @param string widget name
   * @param provider object to handle widget content rendering
   */ 
  public function add_provider($name, $handler) {
    $this->_providers[$name] = $handler;
    return $this;
  }

  /**
   * Get all provider or a particular provider
   */
  public function get_provider($name=NULL) {
    if (!$name) {
      return $this->_providers;
    }
    return $this->_providers[$name];
  }

  /**
   * Register a widget to render it.
   */
  public function register($name) {
    $slugid = self::WIDGET_SLUG_PREFIX . $name;
    $widget_provider = $this->get_provider($name);
    if (empty($widget_provider)) {
      return false;
    }

    wp_add_dashboard_widget(
      $slugid,
      $widget_provider->get_title(),
      array($widget_provider, 'get_content'));
    return true;
  }
}
