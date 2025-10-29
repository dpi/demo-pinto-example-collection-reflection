<?php

declare(strict_types=1);

namespace Drupal\pinto_example_collection_reflection;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\pinto_layout\Discovery\FrozenLayoutDefinition;
use Drupal\pinto_layout\PintoLayout\Data\LayoutData;
use Drupal\pinto_layout\PintoLayout\External\ExternallyDefined;
use Drupal\pinto_layout\PintoLayout\External\ExternallyDefinedInterface;

final class LayoutDefinitions implements ExternallyDefinedInterface {

  public function __construct(
    private Layouts $layouts,
  ) {
  }

  public function getDefinitions(): iterable {
    foreach ($this->layouts->layoutObjects() as $objectClassName) {
      $regions = [];
      foreach ($this->layouts->getRegions($objectClassName) as [$regionName, $regionOrigin]) {
        $regions[] = $regionName;
      }

      yield ExternallyDefined::create(
        id: $objectClassName,
        label: new TranslatableMarkup('Layout from @objectClassName', ['@objectClassName' => $objectClassName]),
        pintoEnum: $this->layouts->getEnum($objectClassName),
        regions: $regions,
        factoryMethod: sprintf('%s::create', static::class),
      );
    }
  }

  public static function create(FrozenLayoutDefinition $frozenLayoutDefinition, LayoutData $layoutData): mixed {
    /** @var class-string $objectClass */
    $objectClass = $frozenLayoutDefinition->layoutId();

    $args = [];
    $args['regionAttributes'] = $layoutData->regionAttributes;
    foreach ($frozenLayoutDefinition->regions->regions as $region) {
      $regionData = $layoutData->regionsData->getRegion($region);
      $args[$region] = new Region(\is_iterable($regionData) ? $regionData : [$regionData]);
    }

    return new $objectClass(...$args);
  }

}
