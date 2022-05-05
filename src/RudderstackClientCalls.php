<?php

namespace Drupal\rudderstack;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Here we interact with the Rudderstack service.
 */
class RudderstackClientCalls {

  /**
   * The client used to send HTTP requests.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * The headers used when sending HTTP request.
   *
   * @var array
   */

  protected $clientHeaders = [
    'Content-Type' => 'application/json',
  ];

  /**
   * The authentication parameters used when calling the remote REST server.
   *
   * @var array
   */

  protected $clientAuth;

  /**
   * The URL of the remote REST server.
   *
   * @var string
   */

  protected $remoteUrl;

  /**
   * The constructor.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The Config Factory.
   */
  public function __construct(ClientInterface $client, ConfigFactoryInterface $config_factory) {
    $this->client = $client;
    $rudderstack_config = $config_factory->get('rudderstack.adminsettings');

    $this->clientAuth = [
      $rudderstack_config->get('rudderstack_write_key'),
      '',
    ];

    $this->remoteUrl = $rudderstack_config->get('rudderstack_dataplane_url');
  }

  /**
   * POST an event to Rudderstack.
   *
   * @param array $event
   *   Contains the data of the event we want to send.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   A HTTP response.
   *
   * @throws \InvalidArgumentException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function post(array $event) {

    if (empty($this->remoteUrl)) {
      $message = t('The remote endpoint URL has not been setup.');
      \Drupal::logger('rudderstack')->error($message);
      return new Response($message, 500);
    }

    $request_body = json_encode($event);

    try {
      $response = $this->client->request(
        'POST',
        $this->remoteUrl,
        [
          'headers' => $this->clientHeaders,
          'auth' => $this->clientAuth,
          'body' => $request_body,
        ]
      );

      // Validate the response from the remote server.
      if ($response->getStatusCode() != 200) {
        $message = t('An error occured while posting to Rudderstack.');
        \Drupal::logger('rudderstack')->error($message);
        return new Response($message, 500);
      }

    }

    catch (RequestException $e) {
      \Drupal::logger('rudderstack')->error($e->getMessage());
    }

  }

}
