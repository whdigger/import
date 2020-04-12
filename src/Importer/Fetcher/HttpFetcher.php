<?php

namespace Drupal\import\Importer\Fetcher;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\import\Entity\ImporterInterface;
use Drupal\import\Importer\Result\HttpFetcherResult;
use Drupal\import\Plugin\Type\Fetcher\FetcherInterface;
use Drupal\import\Plugin\Type\PluginBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines an HTTP fetcher.
 *
 * @ImporterFetcher(
 *   id = "http",
 *   title = @Translation("Import from url"),
 *   description = @Translation("Downloads data from a URL."),
 *   form = {
 *     "import" = "Drupal\import\Importer\Fetcher\Form\HttpFetcherForm",
 *   }
 * )
 */
class HttpFetcher extends PluginBase implements FetcherInterface, ContainerFactoryPluginInterface
{
  /**
   * @var ClientInterface
   */
  protected $client;
  
  /**
   * @var FileSystemInterface
   */
  protected $fileSystem;
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition)
  {
    $instance = parent::create($container, $configuration, $pluginId, $pluginDefinition);
    
    $instance->fileSystem = $container->get('file_system');
    $instance->client = $container->get('http_client');
    
    return $instance;
  }
  
  /**
   * {@inheritdoc}
   */
  public function fetch(ImporterInterface $importer)
  {
    $downloadFilepath = $this->fileSystem->realpath(
      $this->fileSystem->tempnam('temporary://', 'importer.http.fetcher')
    );
    
    $response = $this->get($importer->getSource(), $downloadFilepath);
    
    return new HttpFetcherResult($downloadFilepath, $response->getHeaders());
  }
  
  /**
   * @param $url
   * @param $sink
   *
   * @return mixed
   */
  protected function get($url, $sink)
  {
    $options = [RequestOptions::SINK => $sink];
    
    try {
      $response = $this->client->get($url, $options);
    } catch (RequestException $e) {
      throw new \RuntimeException(
        $this->t(
          'The Rss feed from %site broken due to the error "%error".',
          ['%site' => $url, '%error' => $e->getMessage()])
      );
    }
    
    return $response;
  }
}
