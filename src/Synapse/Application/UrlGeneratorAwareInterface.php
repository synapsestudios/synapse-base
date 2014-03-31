<?php

namespace Synapse\Application;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface UrlGeneratorAwareInterface
{
    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator);
}
