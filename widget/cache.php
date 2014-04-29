<?php
namespace AX\StatBoard\Widget;
use AX\StatBoard\Widget\Provider;

class Cache {
  /**
   * Get cache for a particular widget
   */
  static function get(Provider $provider) {
    echo 'saved';
    $cache_id = get_class($provider);
    if (false !== $data = get_transient($cache_id)) {
      return $data;
    }
    return false;
  }
    
  /**
   * Default we cached 5 minutes
   */
  static function set(Provider $provider, $value, $cache_time = 300) {
    $cache_id = get_class($provider);
    echo 'set';
    set_transient($cache_id, $value, $cache_time);
  }
  
  /**
   * Load data from cache. If not existed, pull the metric and
   * put into cache.
   *
   */
  static function load(Provider $provider, $cache_time) {
    if (false !== $data = static::get($provider)) {
      return $data;
    }
    //no data yet, let's pull it and put it into cache
    $data = $provider->get_metric();
    static::set($provider, $data, $cache_time);
    return $data;
  }

}
