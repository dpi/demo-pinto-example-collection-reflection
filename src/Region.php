<?php

declare(strict_types=1);

namespace Drupal\pinto_example_collection_reflection;

use Drupal\Core\Render\RenderableInterface;
use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<mixed>
 */
final class Region extends AbstractCollection implements RenderableInterface {


  public function getType(): string {
    return 'mixed';
  }

  public function toRenderable() {
    return $this->data;
  }

}
