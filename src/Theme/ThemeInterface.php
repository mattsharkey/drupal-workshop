<?php

namespace Drupal\workshop\Theme;

use Drupal\workshop\Template\TemplateInterface;

interface ThemeInterface
{
    public function getDefaultRegion();

    public function getName();

    public function getRegions();

    /**
     * @param $name
     * @return TemplateInterface
     */
    public function getTemplate($name);

    public function getTemplates();

    public function getTemplatesPath();
}