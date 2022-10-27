<?php

declare(strict_types = 1);

namespace Drupal\Tests\cnt_etranslation\Unit;

use Drupal\cnt_etranslation\Services\DataGeneratorService;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\TestCase;

/**
 * Class MockEntityBuilder
 * Courtesy for: Drupal/Tests/Core/Entity/ContentEntityBaseUnitTest.php
 * @package Drupal\Tests\cnt_etranslation\Unit
 */
class MockEntityBuilder extends TestCase {

  /**
   * The bundle of the entity under test.
   *
   * @var string
   */
  protected $bundle;

  /**
   * The entity under test.
   *
   * @var \Drupal\Core\Entity\ContentEntityBase|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entity;

  /**
   * An entity with no defined language to test.
   *
   * @var \Drupal\Core\Entity\ContentEntityBase|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityUnd;

  /**
   * The entity type used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityType;

  /**
   * The entity field manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityFieldManager;

  /**
   * The entity type bundle manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity type manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The type ID of the entity under test.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The typed data manager used for testing.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $typedDataManager;

  /**
   * The field type manager used for testing.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $fieldTypePluginManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $languageManager;

  /**
   * The UUID generator used for testing.
   *
   * @var \Drupal\Component\Uuid\UuidInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $uuid;

  /**
   * The entity ID.
   *
   * @var int
   */
  protected $id;

  /**
   * Field definitions.
   *
   * @var \Drupal\Core\Field\BaseFieldDefinition[]
   */
  protected $fieldDefinitions;

  /**
   * If you need to configure this service override it.
   */
  public function setEntityType() {
    $this->id = 1;
    $values = [
      'id' => $this->id,
      'uuid' => '3bb9ee60-bea5-4622-b89b-a63319d10b3a',
      'defaultLangcode' => [LanguageInterface::LANGCODE_DEFAULT => 'en'],
    ];

    $this->entityType = $this->createMock('\Drupal\Core\Entity\EntityTypeInterface');
    $this->entityType->expects($this->any())
      ->method('getKeys')
      ->will($this->returnValue([
        'id' => 'id',
        'uuid' => 'uuid',
      ]));
  }

  /**
   * If you need to configure this service override it.
   */
  public function setEntityTypeManager() {
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->entityTypeManager->expects($this->any())
      ->method('getDefinition')
      ->with($this->entityTypeId)
      ->will($this->returnValue($this->entityType));
  }

  /**
   * If you need to configure this service override it.
   */
  public function setEntityFieldManager() {
    $this->entityFieldManager = $this->createMock(EntityFieldManagerInterface::class);
  }

  /**
   * If you need to configure this service override it.
   */
  public function setEntityTypeDataManager() {
    $this->typedDataManager = $this->createMock(TypedDataManagerInterface::class);
    $this->typedDataManager->expects($this->any())
      ->method('getDefinition')
      ->will($this->returnValue(['class' => '\Drupal\Core\Entity\Plugin\DataType\EntityAdapter']));
  }

  /**
   * If you need to configure this service override it.
   */
  public function setLanguageManager() {
    $english = new Language(['id' => 'en']);
    $not_specified = new Language(['id' => LanguageInterface::LANGCODE_NOT_SPECIFIED, 'locked' => TRUE]);
    $this->languageManager = $this->createMock('\Drupal\Core\Language\LanguageManagerInterface');
    $this->languageManager->expects($this->any())
      ->method('getLanguages')
      ->will($this->returnValue(['en' => $english, LanguageInterface::LANGCODE_NOT_SPECIFIED => $not_specified]));
    $this->languageManager->expects($this->any())
      ->method('getLanguage')
      ->with('en')
      ->will($this->returnValue($english));
    $this->languageManager->expects($this->any())
      ->method('getLanguage')
      ->with(LanguageInterface::LANGCODE_NOT_SPECIFIED)
      ->will($this->returnValue($not_specified));
  }

  /**
   * If you need to configure this service override it.
   */
  public function setFieldTypePluginManager() {
    $this->fieldTypePluginManager = $this->getMockBuilder('\Drupal\Core\Field\FieldTypePluginManager')
      ->disableOriginalConstructor()
      ->getMock();
    $this->fieldTypePluginManager->expects($this->any())
      ->method('getDefaultStorageSettings')
      ->will($this->returnValue([]));
    $this->fieldTypePluginManager->expects($this->any())
      ->method('getDefaultFieldSettings')
      ->will($this->returnValue([]));

    $this->fieldTypePluginManager->expects($this->any())
      ->method('createFieldItemList')
      ->will($this->returnValue($this->createMock('Drupal\Core\Field\FieldItemListInterface')));
  }

  /**
   * If you need to set additional services or fields
   * override this method.
   *
   * Overriding requires copy/pasting of the default implementation.
   */
  public function setOtherServices() {
    $this->entityTypeBundleInfo = $this->createMock(EntityTypeBundleInfoInterface::class);
    $this->uuid = $this->createMock('\Drupal\Component\Uuid\UuidInterface');
  }


  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct();

    //@todo: randomly generate these values.
    $this->entityTypeId = 'test_entity_type_id';
    $this->bundle = 'test_bundle';

    $this->setEntityType();
    $this->setEntityTypeManager();
    $this->setEntityFieldManager();
    $this->setEntityTypeDataManager();
    $this->setLanguageManager();
    $this->setFieldTypePluginManager();
    $this->setOtherServices();

    // We have to create actual container as ContentEntityBase class uses it.
    $container = new ContainerBuilder();
    $container->set('entity_type.manager', $this->entityTypeManager);
    $container->set('entity_field.manager', $this->entityFieldManager);
    $container->set('entity_type.bundle.info', $this->entityTypeBundleInfo);
    $container->set('uuid', $this->uuid);
    $container->set('typed_data_manager', $this->typedDataManager);
    $container->set('language_manager', $this->languageManager);
    $container->set('plugin.manager.field.field_type', $this->fieldTypePluginManager);
    \Drupal::setContainer($container);

    $this->entity = $this->getMockForAbstractClass(
      ContentEntityBase::class,
      [$values, $this->entityTypeId, $this->bundle],
      '',
      TRUE,
      TRUE,
      TRUE,
      ['isNew']);
  }

}
