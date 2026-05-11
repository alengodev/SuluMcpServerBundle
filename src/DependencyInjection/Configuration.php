<?php

declare(strict_types=1);

namespace Alengo\SuluMcpServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('alengo_mcp_server');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('token')
                    ->info('Bearer token required in the Authorization header. Empty string disables the API.')
                    ->defaultValue('%env(string:default::MCP_SERVER_TOKEN)%')
                ->end()
                ->arrayNode('template_dirs')
                    ->info('Template type => directory mapping. Paths are relative to %kernel.project_dir%.')
                    ->useAttributeAsKey('type')
                    ->scalarPrototype()->cannotBeEmpty()->end()
                    ->defaultValue([
                        'page' => 'config/templates/pages',
                        'article' => 'config/templates/articles',
                        'block' => 'config/templates/blocks/content',
                        'snippet' => 'config/templates/snippets',
                        'property' => 'config/templates/properties',
                    ])
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
