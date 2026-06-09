<?php

declare(strict_types=1);

namespace Alengo\SuluMcpServerBundle\DependencyInjection;

use Alengo\SuluMcpServerBundle\Controller\TemplateController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;

class McpServerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $controllerDef = new Definition(TemplateController::class);
        $controllerDef->addArgument('%kernel.project_dir%');
        $controllerDef->addArgument($config['template_dirs']);
        $controllerDef->setPublic(true);

        $container->setDefinition(TemplateController::class, $controllerDef);
    }

    public function getAlias(): string
    {
        return 'alengo_mcp_server';
    }
}
