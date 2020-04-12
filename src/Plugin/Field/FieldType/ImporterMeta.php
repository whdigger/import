<?php

namespace Drupal\import\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\TypedData\DataDefinition;

/**
 *
 * @FieldType(
 *   id = "importer_meta",
 *   label = @Translation("Import module"),
 *   description = @Translation("Import metadata."),
 *   no_ui = TRUE,
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList",
 * )
 */
class ImporterMeta extends EntityReferenceItem implements FieldItemInterface
{
  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = true)
  {
    if (isset($values['hash']) && empty($values['hash'])) {
      // Set url explicitly to NULL to prevent validation errors.
      $values['hash'] = null;
    }
    
    return parent::setValue($values, $notify);
  }
  
  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings()
  {
    return ['target_type' => 'importer'] + parent::defaultStorageSettings();
  }
  
  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition)
  {
    $properties = parent::propertyDefinitions($field_definition);
    
    $properties['hash'] = DataDefinition::create('string')
      ->setLabel(t('Item hash'));
    
    return $properties;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $fieldDefinition)
  {
    return [
      'columns'      => [
        'target_id' => [
          'description' => 'The ID of the importer.',
          'type' => 'int',
          'not null' => TRUE,
          'unsigned' => TRUE,
        ],
        'hash' => [
          'type'        => 'varchar',
          'length'      => 32,
          'not null'    => true,
          'description' => 'Uniq primary key.',
          'is_ascii'    => true,
        ],
      ],
      'indexes' => [
        'target_id' => ['target_id'],
      ],
      'foreign keys' => [
        'target_id' => [
          'table'   => 'importer',
          'columns' => ['target_id' => 'id'],
        ],
      ],
    ];
  }
}
