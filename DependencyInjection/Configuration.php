<?php

namespace Liplex\Bundle\MultipleFileUploadBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Build configuration for multiple file upload
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('liplex_multiple_file_upload');

        $rootNode
            ->children()
                ->scalarNode('media_class')
                    ->info('Custom media class generated with the media bundle')
                    ->isRequired()
                ->end()
                ->arrayNode('mapping')
                    ->isRequired()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('class')
                                ->info('Full class name with namespace')
                            ->end()
                            ->arrayNode('fields')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('field')
                                            ->info('Field name of the entity')
                                        ->end()
                                        ->scalarNode('context')
                                            ->info('Media context for field media')
                                        ->end()
                                        ->scalarNode('provider')
                                            ->info('Name of the provider for the field')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
