<?php

namespace Drupal\import\Importer\Result;

class HttpFetcherResult extends FetcherBaseResult implements HttpFetcherResultInterface
{
  /**
   * @var array
   */
  protected $headers;
  
  public function __construct(string $filePath, array $headers)
  {
    parent::__construct($filePath);
    $this->headers = array_change_key_case($headers);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getHeaders(): array
  {
    return $this->headers;
  }
  
}
