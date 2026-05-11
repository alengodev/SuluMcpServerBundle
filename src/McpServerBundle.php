<?php

declare(strict_types=1);

namespace Alengo\SuluMcpServerBundle;

use Alengo\SuluMcpServerBundle\DependencyInjection\McpServerExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class McpServerBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new McpServerExtension();
        }

        return $this->extension;
    }
}
