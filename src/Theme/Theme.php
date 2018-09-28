<?php

namespace Drupal\workshop\Theme;

use Drupal\Core\Extension\Extension;
use Drupal\workshop\Template\TemplateCollection;
use Drupal\workshop\Template\TemplateCollectionInterface;

class Theme implements ThemeInterface
{
    private $name;

    /**
     * @var TemplateCollectionInterface
     */
    private $templates;

    /**
     * @var Extension
     */
    private $theme;

    public function __construct($name)
    {
        $this->name = $name;
        $themes = system_list('theme');
        if (!isset($themes[$name])) {
            throw new \OutOfBoundsException("Unknown theme: $name");
        }
        $this->theme = $themes[$name];
        $this->templates = TemplateCollection::forTheme($this);
    }

    public function getDefaultRegion()
    {
        $regions = $this->getRegions();
        if (isset($regions['content'])) {
            return 'content';
        }
        $keys = array_keys($regions);
        return $keys[0];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRegions()
    {
        return $this->theme->info['regions'];
    }

    public function getTemplate($name)
    {
        return $this->getTemplates()->get($name);
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    public function getTemplatesPath()
    {
        return $this->getPath() . DIRECTORY_SEPARATOR . 'templates';
    }

    private function getPath()
    {
        return $this->theme->getPath();
    }
}