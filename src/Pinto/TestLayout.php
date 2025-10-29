<?php

declare(strict_types=1);

namespace Drupal\pinto_example_collection_reflection\Pinto;

use Drupal\pinto\Object\DrupalObjectTrait;
use Drupal\pinto_example_collection_reflection\Region;
use Drupal\pinto_layout\PintoLayout\Data\RegionAttributes;
use Pinto\Slots\Build;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<mixed>
 */
final class TestLayout extends AbstractCollection {

  use DrupalObjectTrait;

  /**
   * @phpstan-param \Drupal\pinto_example_collection_reflection\Region<mixed> $leftSide
   * @phpstan-param \Drupal\pinto_example_collection_reflection\Region<mixed> $rightSide
   * @phpstan-param \Drupal\pinto_example_collection_reflection\Region<mixed> $content
   * @phpstan-param array<string, mixed> $attributes
   */
  public function __construct(
    public RegionAttributes $regionAttributes,
    public Region $leftSide,
    public Region $rightSide,
    public Region $content,
    public array $attributes = [],
  ) {
    parent::__construct(\iterator_to_array($content));
  }

  public function getType(): string {
    return 'mixed';
  }

  public function __invoke(): mixed {
    return $this->pintoBuild(function (Build $build): Build {
      return $build
        ->set('attributes', $this->regionAttributes->containerAttributes())
        ->set('regionAttributes', $this->regionAttributes->regionsAsArray());
    });
  }

}
