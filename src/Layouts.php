<?php

declare(strict_types=1);

namespace Drupal\pinto_example_collection_reflection;

use GoodPhp\Reflection;
use GoodPhp\Reflection\Reflector;
use Pinto\Attribute\Definition;
use Pinto\List\ObjectListInterface;
use Pinto\PintoMapping;

final class Layouts {

  private static Reflector $reflector;

  /**
   * @var array<class-string, array{ObjectListInterface, array{string, string}}>
   */
  private array $discovery;

  public function __construct(
    private PintoMapping $pintoMapping,
  ) {
    // Build once as it's expensive.
    $this::$reflector ??= (new Reflection\ReflectorBuilder())
      ->withFileCache()
      ->withMemoryCache()
      ->build();
  }

  /**
   * @return iterable<class-string>
   */
  public function layoutObjects(): iterable {
    $this->discovery();

    yield from array_keys($this->discovery);
  }

  /**
   * @param class-string $objectClassName
   * @return array{string, string}
   * @throws \InvalidArgumentException
   * @internal
   */
  public function getRegions(string $objectClassName): array {
    $this->discovery();

    return \array_key_exists($objectClassName, $this->discovery) ? $this->discovery[$objectClassName][1] : throw new \InvalidArgumentException();
  }

  /**
   * @param class-string $objectClassName
   * @throws \InvalidArgumentException
   * @internal
   */
  public function getEnum(string $objectClassName): ObjectListInterface {
    $this->discovery();

    return \array_key_exists($objectClassName, $this->discovery) ? $this->discovery[$objectClassName][0] : throw new \InvalidArgumentException();
  }

  public function discovery(): void {
    if (isset($this->discovery)) {
      return;
    }

    $objectClassNames = [];

    foreach ($this->pintoMapping->getEnumClasses() as $pintoList) {
      foreach ($pintoList::cases() as $case) {
        $rCase = new \ReflectionEnumUnitCase($case::class, $case->name);
        $definitionAttr = ($rCase->getAttributes(Definition::class)[0] ?? NULL)?->newInstance();
        if ($definitionAttr === NULL) {
          continue;
        }

        if (\str_ends_with($definitionAttr->className, 'Layout')) {
          $objectClassNames[] = [$definitionAttr->className, $case];
        }
      }
    }

    $this->discovery = \array_map(function (array $data): array {
      [$objectClassName, $case] = $data;

      $regions = [];
      $rObj = new \ReflectionClass($objectClassName);
      $rClass = $this::$reflector->forType($objectClassName);

      /// If it's an ArrayAccess then make that the content region.
      if ($rObj->implementsInterface(\ArrayAccess::class)) {
        $setType = $rClass->method('offsetSet')?->parameter('value')?->type();
        if ((string) $setType === 'mixed') {
          $regions[] = ['content', '!root'];
        }
      }

      foreach ($rClass->constructor()->parameters() as $param) {
        $paramType = $param->type();
        $paramClass = $paramType->name;
        if (!$paramType instanceof Reflection\Type\NamedType || !\class_exists($paramClass)) {
          continue;
        }

        $rClass = new \ReflectionClass($paramClass);
        if (!$rClass->implementsInterface(\ArrayAccess::class)) {
          continue;
        }

        // If it's an ArrayAccess that takes `mixed` then make it a region.
        $paramNamedType = $this::$reflector->forNamedType($paramType);
        $setType = $paramNamedType->method('offsetSet')?->parameter('value')?->type();
        if ((string) $setType === 'mixed') {
          $regions[] = [$param->name(), 'constructorParam:' . $param->name()];
        }
      }

      return [$case, $regions];
    }, \Safe\array_combine(\array_column($objectClassNames, 0), $objectClassNames));
  }


}
