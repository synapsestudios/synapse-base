<?php

namespace Synapse\Application;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface UrlGeneratorAwareInterface
{
    /**
     * @param UrlGenerator $urlGenerator
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator);
}
