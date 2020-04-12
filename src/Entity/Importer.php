<?php

namespace Drupal\import\Entity;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Importer entity.
 *
 * @property-read \Drupal\Core\Field\EntityReferenceFieldItemList $type
 *
 * @ingroup import
 *
 * @ContentEntityType(
 *   id = "importer",
 *   label = @Translation("Importer"),
 *   bundle_label = @Translation("Importer type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\import\ImporterListBuilder",
 *     "views_data" = "Drupal\import\Entity\ImporterViewsData",
 *     "form" = {
 *       "default" = "Drupal\import\Form\ImporterForm",
 *       "add" = "Drupal\import\Form\ImporterForm",
 *       "edit" = "Drupal\import\Form\ImporterForm",
 *       "delete" = "Drupal\import\Form\ImporterDeleteForm",
 *       "import" = "Drupal\import\Form\ImporterImportForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\import\ImporterHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\import\ImporterAccessControlHandler",
 *     "import_handler" = "Drupal\import\ImporterImportHandler",
 *   },
 *   base_table = "importer",
 *   fieldable = TRUE,
 *   admin_permission = "administer importer entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/importer/{importer}",
 *     "add-page" = "/admin/structure/importer/add",
 *     "add-form" = "/admin/structure/importer/add/{importer_type}",
 *     "edit-form" = "/admin/structure/importer/{importer}/edit",
 *     "delete-form" = "/admin/structure/importer/{importer}/delete",
 *     "import-collection" = "/admin/content/importer",
 *     "import" = "/admin/structure/importer/{importer}/import",
 *   },
 *   bundle_entity_type = "importer_type",
 *   field_ui_base_route = "entity.importer_type.edit_form"
 * )
 */
class Importer extends ContentEntityBase implements ImporterInterface
{
  
  use EntityChangedTrait;
  
  /**
   * {@inheritdoc}
   */
  public function getName()
  {
    return $this->get('name')->value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setName($name)
  {
    $this->set('name', $name);
    
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getCreatedTime()
  {
    return $this->get('created')->value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp)
  {
    $this->set('created', $timestamp);
    
    return $this;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getType()
  {
    return $this->type->entity;
  }
  
  /**
   * {@inheritdoc}
   */
  public function getSource()
  {
    return $this->source->value;
  }
  
  /**
   * {@inheritdoc}
   */
  public function setSource($source)
  {
    return $this->set('source', $source);
  }
  
  /**
   * {@inheritdoc}
   */
  public function startImport()
  {
    $this->entityTypeManager()
      ->getHandler('importer', 'import_handler')
      ->import($this);
  }
  
  /**
   * {@inheritdoc}
   */
  public function lock()
  {
    if (!\Drupal::service('lock.persistent')->acquire("importer_{$this->id()}", 3600 * 12)) {
      
      throw new LockException(
        new FormattableMarkup(
          'Unable to get a block for bundle: %bundle.',
          ['%bundle' => $this->bundle()]
        )
      );
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function unlock()
  {
    \Drupal::service('lock.persistent')->release("importer_{$this->id()}");
  }
  
  /**
   * {@inheritdoc}
   */
  public function isLocked()
  {
    return !\Drupal::service('lock.persistent')->lockMayBeAvailable("importer_{$this->id()}");
  }
  
  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
  {
    $fields = [];
    
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('ID.'))
      ->setReadOnly(true)
      ->setSetting('unsigned', true);
    
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('UUID.'))
      ->setReadOnly(true);
    
    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Import type'))
      ->setDescription(t('Import type.'))
      ->setSetting('target_type', 'importer_type')
      ->setReadOnly(true);
    
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title.'))
      ->setRequired(true)
      ->setDefaultValue('')
      ->setSetting('max_length', 255)
      ->setDisplayOptions('view', [
        'label'  => 'hidden',
        'type'   => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', true);
    
    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Importing status'))
      ->setDescription(t('A boolean indicating when is active.'))
      ->setDefaultValue(true);
    
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));
    
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));
    
    $fields['source'] = BaseFieldDefinition::create('uri')
      ->setLabel(t('Source'))
      ->setDescription(t('The source uri.'))
      ->setRequired(true)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type'  => 'import_uri_link',
      ])
      ->setDisplayConfigurable('view', true);
    
    $fields['time_imported'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Last import'))
      ->setDescription(t('Time when the import was made.'))
      ->setDefaultValue(0);
    
    return $fields;
  }
  
}
