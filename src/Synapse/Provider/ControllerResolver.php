<?php

namespace Synapse\Provider;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Synapse\Controller\AbstractRestController;
use Synapse\Rest\Exception\MethodNotImplementedException;

/**
 * Enables name_of_service:rest, name_of_service:method_name syntax for
 * declaring controllers.
 *
 * @link http://silex.sensiolabs.org/doc/providers/service_controller.html
 */
class ControllerResolver implements ControllerResolverInterface
{
    const SERVICE_PATTERN = "/[A-Za-z0-9\._\-]+:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/";

    protected $resolver;
    protected $app;

    /**
     * Constructor.
     *
     * @param ControllerResolverInterface $resolver A ControllerResolverInterface instance to delegate to
     * @param Application                 $app      An Application instance
     */
    public function __construct(ControllerResolverInterface $resolver, Application $app)
    {
        $this->resolver = $resolver;
        $this->app      = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        $controller = $request->attributes->get('_controller', null);

        if (!is_string($controller) || !preg_match(static::SERVICE_PATTERN, $controller)) {
            return $this->resolver->getController($request);
        }

        list($service, $method) = explode(':', $controller, 2);

        if (!isset($this->app[$service])) {
            throw new \InvalidArgumentException(sprintf('Service "%s" does not exist.', $service));
        }

        if ($method === 'rest') {
            return [$this->app[$service], 'execute'];
        } else {
            return [$this->app[$service], $method];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments(Request $request, $controller)
    {
        return $this->resolver->getArguments($request, $controller);
    }
}
