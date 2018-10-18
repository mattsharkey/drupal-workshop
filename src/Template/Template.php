<?php

namespace Drupal\workshop\Template;

class Template implements TemplateInterface
{
    private $file;

    private $name;

    public function __construct($name, \SplFileInfo $file)
    {
        $this->name = $name;
        $this->file = $file;
    }

    public function __toString()
    {
        return (string)$this->getName();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTime()
    {
        return $this->file->getMTime();
    }
}