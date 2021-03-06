<?php
/**
 * @file
 * StaticController.php
 *
 * @author: Frédéric G. MARAND <fgm@osinet.fr>
 *
 * @copyright (c) 2015 Ouest Systèmes Informatiques (OSInet).
 *
 * @license General Public License version 2 or later
 */

namespace OSInet\Lazy\Controller;

/**
 * Class StaticController is the fastest and most limited of the Lazy
 * controllers: it serves any data available, and a fixed render array if no
 * data is available.
 *
 * @package OSInet\Lazy
 */
class StaticController extends MissController {
  /**
   * Render the builder results in a front-end page cycle.
   *
   * @param string $cid
   * @param string $lock_name
   *
   * @return mixed
   */
  protected function renderFront($cid, $lock_name) {
    watchdog('lazy', 'Base @class/method:<pre><code>controller</code></pre>', [
      '@class' => get_called_class(),
      '@method' => __METHOD__,
      '@controller' => var_export($this, true),
    ], WATCHDOG_DEBUG);

    $ret = [
      '#markup' => t('Content is being updated, please wait for a few seconds.'),
    ];

    $passes = 0;
    $lock_name = __METHOD__;
    while ($passes < static::MAX_PASSES) {
      if ($this->lockAcquire($lock_name)) {
        watchdog('lazy', "Queueing rebuild for lock @lock", ['@lock' => $lock_name], WATCHDOG_DEBUG);
        $queue = $this->getQueue();
        $queue->createItem($this);
        lock_release($lock_name);
        break;
      }
      $passes++;
    }

    return $ret;
  }
}
