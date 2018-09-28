<?php

namespace Drupal\workshop\Template;

use Drupal\workshop\Theme\ThemeInterface;
use Traversable;

class TemplateCollection implements TemplateCollectionInterface, \Countable, \IteratorAggregate
{
    private $templates = [];

    public static function forTheme(ThemeInterface $theme)
    {
        $dir = $theme->getTemplatesPath();
        if (!is_dir($dir)) {
            throw new \DomainException("Not a directory: $dir");
        }
        $iterator = new TemplateFilterIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)));
        $collection = new static();
        foreach ($iterator as $file) {
            $name = $iterator->getInnerIterator()->getInnerIterator()->getSubPathname();
            $collection->templates[$name] = new Template($theme, $name, $file);
        }
        return $collection;
    }

    public function count()
    {
        return count($this->getTemplates());
    }

    public function get($name)
    {
        if (!isset($this->templates[$name])) {
            throw new \Exception("Unknown template: $name");
        }
        return $this->templates[$name];
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getTemplates());
    }

    public function getTemplates()
    {
        return $this->templates;
    }
}