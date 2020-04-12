<?php

namespace Drupal\import\Importer\Parser;

use Drupal\import\Entity\ImporterInterface;
use Drupal\import\Importer\Item\RssItem;
use Drupal\import\Importer\Result\FetcherResultInterface;
use Drupal\import\Importer\Result\ParserResult;
use Drupal\import\Plugin\Type\Parser\ParserInterface;
use Drupal\import\Plugin\Type\PluginBase;
use Zend\Feed\Reader\Exception\ExceptionInterface;
use Zend\Feed\Reader\Reader;

/**
 * RSS-feed parser.
 *
 * @ImporterParser(
 *   id = "rss",
 *   title = @Translation("RSS"),
 *   description = @Translation("Default parser for RSS feeds.")
 * )
 */
class RssParser extends PluginBase implements ParserInterface
{
  /**
   * {@inheritdoc}
   */
  public function parse(ImporterInterface $importer, FetcherResultInterface $fetcherResult)
  {
    Reader::setExtensionManager(\Drupal::service('feed.bridge.reader'));
    
    $result = new ParserResult();
    $fetchContent = $fetcherResult->getContent();
    if (!strlen($fetchContent)) {
      return $result;
    }
    
    try {
      $channel = Reader::importString($fetchContent);
    } catch (ExceptionInterface $e) {
      throw new \RuntimeException(
        $this->t(
          'The Rss feed from %site  broken due to the error "%error".',
          ['%site' => $importer->label(), '%error' => trim($e->getMessage())]
        )
      );
    }
    
    foreach ($channel as $delta => $entry) {
      $item = new RssItem();
      
      $content = $entry->getContent();
      
      $item
        ->set('title', $entry->getTitle())
        ->set('guid', $entry->getId())
        ->set('link', $entry->getLink())
        ->set('content', $content);
      
      if ($content) {
        $re = '/<img\b[^>]+?src\s*=\s*[\'"]?(?P<link>[^\s\'"?#>]+)/m';
        preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);
        if (count($matches)) {
          $item->set('contentFirstImage', array_shift($matches)['link']);
        }
      }
      
      if ($enclosure = $entry->getEnclosure()) {
        $item->set('enclosures', [rawurldecode($enclosure->url)]);
      }
      
      $result->addItem($item);
    }
    
    return $result;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getMappingSources()
  {
    return [
      'fields'        => [
        'title'             => $this->t('Title'),
        'guid'              => $this->t('GUID'),
        'link'              => $this->t('URL link'),
        'content'           => $this->t('Content'),
        'contentFirstImage' => $this->t('First image in content'),
        'enclosures'        => $this->t('Multimedia resources in article'),
      ],
    ];
  }
  
}
