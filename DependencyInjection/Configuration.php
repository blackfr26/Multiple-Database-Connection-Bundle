<?php
 
namespace DesarrolloHosting\MultipleDbConnectionBundle\DependencyInjection;
 
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
 
/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('multiple_db_connection');
 
        $rootNode
            ->children()
                ->arrayNode('services')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('databases')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('host')->isRequired()->end()
                                        ->scalarNode('dbname')->isRequired()->end()
                                        ->scalarNode('user')->isRequired()->end()
                                        ->scalarNode('password')->isRequired()->end()
                                        ->scalarNode('charset')->defaultValue('UTF8')->end()
                                    ->end()
                                ->end()
                            ->end() //databases
                        ->end()
                    ->end()//prototype
                ->end()//systems
            ->end()
        ;
 
        return $treeBuilder;
    }
}