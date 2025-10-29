<?php

declare(strict_types=1);

namespace Drupal\pinto_example_collection_reflection\Pinto;

use Pinto\Attribute\Asset\Css;
use Pinto\Attribute\Definition;
use Pinto\Attribute\ObjectType\Slots;
use Pinto\List\ObjectListInterface;
use Pinto\List\ObjectListTrait;
use function Safe\realpath;

#[Slots(bindPromotedProperties: TRUE)]
#[Css('styles.css')]
enum TestList implements ObjectListInterface {

  use ObjectListTrait;

  #[Definition(TestLayout::class)]
  case TestLayout;

  public function templateDirectory(): string {
    return '@pinto_example_collection_reflection/templates/';
  }

  public function templateName(): string {
    return $this->name();
  }

  public function cssDirectory(): string {
    return realpath(__DIR__ . '/../../css');
  }

  public function jsDirectory(): string {
    return realpath(__DIR__ . '/../../js');
  }

}
