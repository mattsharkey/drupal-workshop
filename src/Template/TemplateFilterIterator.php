<?php

namespace Drupal\workshop\Template;

class TemplateFilterIterator extends \RegexIterator
{
    public function __construct(\Iterator $iterator)
    {
        parent::__construct($iterator, $this->getTemplatePattern(), static::MATCH);
    }

    private function getTemplatePattern()
    {
        return '#' . preg_quote(Template::EXTENSION) . '$#';
    }
}