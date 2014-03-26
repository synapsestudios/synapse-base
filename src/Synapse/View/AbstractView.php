<?php

namespace Synapse\View;

/**
 * Abstract view class, to abstract the rendering of a template using mustache.
 *
 * Automatically searches for a template in a corresponding path of the template directory.
 */
abstract class AbstractView
{
    /**
     * @var Mustache_Engine
     */
    protected $mustache;

    /**
     * Set injected dependencies
     * @param Mustache_Engine $mustache
     */
    public function __construct($mustache)
    {
        $this->mustache = $mustache;
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * Return the string representation of this rendered view
     * @return string
     */
    public function render()
    {
        $template = $this->template();

        return $this->mustache->render($template, $this);
    }

    /**
     * Return the corresponding template file name for this class minus file extension
     * @return string
     */
    protected function template()
    {
        $reflector = new \ReflectionClass(get_class($this));
        $classname = $reflector->getName();

        $template = preg_replace('/(Application|Synapse)\\\View\\\/', '', $classname);
        $template = str_replace('\\', '/', $template);

        return $template;
    }
}
