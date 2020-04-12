<?php

namespace Drupal\import\Importer\Result;

use Drupal\Component\Render\FormattableMarkup;

/**
 * Class FetcherBaseResult
 * @package Drupal\import\Importer\Result
 */
class FetcherBaseResult implements FetcherResultInterface
{
  /**
   * @var string
   */
  protected $filePath;
  
  /**
   * @param string $filePath
   */
  public function __construct($filePath)
  {
    $this->filePath = $filePath;
    $this->checkFile();
  }
  
  /**
   * {@inheritdoc}
   */
  public function getContent()
  {
    return trim(file_get_contents($this->filePath));
  }
  
  /**
   * {@inheritdoc}
   */
  public function getFilePath(): string
  {
    return $this->filePath;
  }
  
  protected function checkFile()
  {
    if (!file_exists($this->filePath)) {
      throw new \RuntimeException(
        new FormattableMarkup('File %filepath does not exist.', ['%filepath' => $this->filePath])
      );
    }
    
    if (!is_readable($this->filePath)) {
      throw new \RuntimeException(
        new FormattableMarkup('File %filepath is not readable.', ['%filepath' => $this->filePath])
      );
    }
  }
}
