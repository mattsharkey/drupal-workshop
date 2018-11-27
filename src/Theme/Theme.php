<?php

namespace Drupal\workshop\Theme;

use CreativeServices\Workshop\Template\File\TemplateDirectory;
use Drupal\Core\Extension\Extension;
use Symfony\Component\Yaml\Yaml;

class Theme implements ThemeInterface
{
    /**
     * @var array
     */
    private $info;

    /**
     * @var string
     */
    private $name;

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
    }

    public function getFixturesPath()
    {
        $info = $this->getWorkshopInfo();
        if (isset($info['fixtures'])) {
            return $this->makePath($info['fixtures']);
        } else {
            return $this->getWorkshopPath();
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTemplates()
    {
        return new TemplateDirectory($this->getWorkshopPath());
    }

    public function getTemplatesPath()
    {
        return $this->makePath($this->getTemplatesDirectory());
    }

    public function getWorkshopDirectory()
    {
        $info = $this->getWorkshopInfo();
        return isset($info['directory']) ? $info['directory'] : null;
    }

    public function getWorkshopLibraries()
    {
        $libraries = [];
        $info = $this->getWorkshopInfo();
        if (isset($info['libraries'])) {
            $libraries = (array)$info['libraries'];
        }
        return $libraries;
    }

    public function getWorkshopPath()
    {
        return $this->makePath($this->getTemplatesDirectory(), $this->getWorkshopDirectory());
    }

    /**
     * @param string $name
     * @return string
     */
    public function makeTemplateName($name)
    {
        return '@' . $this->getName() . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * @return array
     */
    private function getInfo()
    {
        if (!isset($this->info)) {
            $this->info = Yaml::parseFile($this->getInfoPath());
        }
        return $this->info;
    }

    /**
     * @return string
     */
    private function getInfoPath()
    {
        return implode(DIRECTORY_SEPARATOR, [DRUPAL_ROOT, $this->theme->getPathname()]);
    }

    /**
     * @return string
     */
    private function getPath()
    {
        return $this->theme->getPath();
    }

    /**
     * @return string
     */
    private function getTemplatesDirectory()
    {
        return 'templates';
    }

    /**
     * @return array
     */
    private function getWorkshopInfo()
    {
        $info = $this->getInfo();
        return isset($info['workshop']) ? $info['workshop'] : [];
    }

    /**
     * @return string
     */
    private function makePath()
    {
        $segments = func_get_args();
        array_unshift($segments, $this->getPath());
        array_unshift($segments, DRUPAL_ROOT);
        return implode(DIRECTORY_SEPARATOR, array_filter($segments));
    }
}