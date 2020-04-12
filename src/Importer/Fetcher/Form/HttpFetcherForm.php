<?php

namespace Drupal\import\Importer\Fetcher\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\import\Entity\ImporterInterface;
use Drupal\import\Importer\Helper\ImporterHelper;
use Drupal\import\Plugin\Type\ConfigurationPluginFormBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HttpFetcherForm
 * @package Drupal\import\Importer\Fetcher\Form
 */
class HttpFetcherForm extends ConfigurationPluginFormBase implements ContainerInjectionInterface
{
  /**
   * @var ClientInterface
   */
  protected $client;
  
  /**
   * @param ClientInterface $client
   */
  public function __construct(ClientInterface $client)
  {
    $this->client = $client;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static($container->get('http_client'));
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $formState, ImporterInterface $importer = null)
  {
    $form['source'] = [
      '#title'         => $this->t('Import URL'),
      '#type'          => 'url',
      '#default_value' => $importer->getSource(),
      '#maxlength'     => 2048,
      '#required'      => true,
    ];
    
    return $form;
  }
  
  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $formState)
  {
    $url = $formState->getValue('source');
    try {
      ImporterHelper::isValidSchema($url);
      $this->client->get($url);
    } catch (\InvalidArgumentException $e) {
      $formState->setError($form['source'], $this->t("The url's scheme is not supported. Supported schemes are: %supported.",
        ['%supported' => implode(', ', ImporterHelper::getSupportedSchemes())])
      );
    } catch (RequestException $e) {
      $formState->setError($form['source'], $this->t('When accessing the %site site an error "%error" occurred.',
        ['%site' => $url, '%error' => $e->getMessage()])
      );
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state, ImporterInterface $importer = null)
  {
    $importer->setSource($form_state->getValue('source'));
  }
}
