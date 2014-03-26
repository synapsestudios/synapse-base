<?php

namespace Synapse;

use Silex\Application as SilexApp;
use Silex\Application\SecurityTrait;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Request;

/**
 * Silex Application extended
 */
class Application extends SilexApp
{
    use SecurityTrait;

    /**
     * Controller initializers, used to perform a callback on controllers that implement a given interface
     *
     * @var array
     */
    protected $initializers = array();

    /**
     * Set the route class
     *
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        $this['route_class'] = 'Synapse\\Route';
    }

    /**
     * Override Pimple's offsetGet to add support for initializers
     *
     * @param  string $id The unique identifier for the parameter or object
     * @return mixed      The value of the parameter or an object
     */
    public function offsetGet($id)
    {
        $value = parent::offsetGet($id);

        if (is_object($value)) {
            $this->initialize($value);
        }

        return $value;
    }

    /**
     * Initialize an object
     *
     * @param  mixed $object Object to be initialized
     * @return mixed
     */
    protected function initialize($object)
    {
        foreach ($this->initializers as $initializer) {
            if ($object instanceof $initializer['class']) {
                $initializer['callable']($object);
            }
        }

        return $object;
    }

    /**
     * Add an initializer
     * @param  string   $class    the initializer will apply to objects of this class
     * @param  callable $callable the callable to run on objects
     * @return object
     */
    public function initializer($class, callable $callable)
    {
        $this->initializers[] = array(
            'class'    => $class,
            'callable' => $callable,
        );

        return $this;
    }

    /**
     * Overrides Silex's Application::run to handle console requests instead
     * of HTTP requests if the PHP SAPI name is cli
     *
     * {@inheritDoc}
     */
    public function run(Request $request = null)
    {
        if (php_sapi_name() !== 'cli') {
            return parent::run($request);
        }

        if (!$this->booted) {
            $this->boot();
        }

        $this['console']->run();
    }

    /**
     * Add a command to $this['console'] (Symfony's console component)
     */
    public function command($command)
    {
        if (! $command instanceof Command) {
            $command = $this[$command];
        }

        $this['console']->add($command);
    }
}
