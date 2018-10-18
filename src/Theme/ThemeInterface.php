<?php

namespace Drupal\workshop\Theme;

interface ThemeInterface
{
    public function getName();

    public function getFixturesPath();

    public function getTemplatesPath();

    public function getWorkshopDirectory();

    public function getWorkshopLibraries();

    public function getWorkshopPath();
}