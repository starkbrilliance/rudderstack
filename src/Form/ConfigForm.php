<?php

/**
 * @file
 * Contains Drupal\rudderstack\Form\ConfigForm.
 */

namespace Drupal\rudderstack\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ConfigForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'rudderstack.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rudderstack_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('rudderstack.adminsettings');

    $form['rudderstack_write_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Rudderstack Write Key'),
      '#default_value' => $config->get('rudderstack_write_key') ?? [],
    ];

    $form['rudderstack_dataplane_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Rudderstack Dataplane URL'),
      '#default_value' => $config->get('rudderstack_dataplane_url') ?? [],
    ];

    $form['rudderstack_config_events'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Configuration events to track'),
      '#options' => array(
        'config_save' => t('Configuration Save Events'),
        'config_delete' => t('Configuration Delete Events'),
      ),
      '#default_value' => $config->get('rudderstack_config_events') ?? [],
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('rudderstack.adminsettings')
      ->set('rudderstack_write_key', $form_state->getValue('rudderstack_write_key'))
      ->set('rudderstack_dataplane_url', $form_state->getValue('rudderstack_dataplane_url'))
      ->set('rudderstack_config_events', $form_state->getValue('rudderstack_config_events'))
      ->save();
  }


}
