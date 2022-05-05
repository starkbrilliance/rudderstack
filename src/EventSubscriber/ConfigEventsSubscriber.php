<?php

namespace Drupal\rudderstack\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\rudderstack\RudderstackClientCalls;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EntityTypeSubscriber
 *
 * @package Drupal\rudderstack\EventSubscriber
 */
class ConfigEventsSubscriber implements EventSubscriberInterface {

  /**
   * RudderstackClientCalls object.
   *
   * @var \Drupal\rudderstack\RudderstackClientCalls
   */
  private $rudderstackClientCalls;

  /**
   * RudderstackClientController constructor.
   *
   * @param \Drupal\rudderstack\RudderstackClientCalls $rudderstack_client_calls
   *   RudderstackClientCalls service.
   */
  public function __construct(RudderstackClientCalls $rudderstack_client_calls) {
    $this->rudderstackClientCalls = $rudderstack_client_calls;
  }

  /**
   * {@inheritDoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
  */
  public static function getSubscribedEvents() {
    return [
      ConfigEvents::SAVE => 'configSave',
      ConfigEvents::DELETE => 'configDelete',
    ];
  }

  /**
   * React to a config object being saved.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   Config crud event.
   */
  public function configSave(ConfigCrudEvent $event) {
    if($this->checkConfigEventIsToBeTracked('config_save')) {
      $this->handleConfigEvent($event, 'Saved');
    }
  }

  /**
   * React to a config object being deleted.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   Config crud event.
   */
  public function configDelete(ConfigCrudEvent $event) {
    if ($this->checkConfigEventIsToBeTracked('config_delete')) {
      $this->handleConfigEvent($event, 'Deleted');
    }
  }

  /**
   * Helper function to check if config event is to be tracked or not.
   *
   * @param string $tracked_config_value
   *   The checkbox value from the rudderstack adminconfig form to be checked.
   */
  private function checkConfigEventIsToBeTracked(string $tracked_config_value) {
    $trackedConfigEvents = \Drupal::config('rudderstack.adminsettings')->get('rudderstack_config_events');
    if (is_array($trackedConfigEvents) && isset($trackedConfigEvents[$tracked_config_value])) {
      // Our value is a boolean, TRUE if checked, FALSE if not - we'll pass that on back
      if ($trackedConfigEvents[$tracked_config_value]) {
        return TRUE;
      }
    }
  }

  /**
   * Handle a configuration event.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   Config crud event.
   * @param string $operation
   *   CRUD operation performed.
   */
  private function handleConfigEvent(ConfigCrudEvent $event, $operation) {
    $config = $event->getConfig();
    $post = [
      "event" => "$operation config: " . $config->getName(),
      "userId" => \Drupal::currentUser()->id(),
    ];
    $this->rudderstackClientCalls->post($post);
  }

}
