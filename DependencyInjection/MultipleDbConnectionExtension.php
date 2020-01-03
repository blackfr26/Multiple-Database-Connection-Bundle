<?php
 
namespace DesarrolloHosting\MultipleDbConnectionBundle\DependencyInjection;
 
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
 
/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class MultipleDbConnectionExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        //dynamicaly load services
        //example of config.yml
        //------------------------
//        multiple_db_connection:
//            systems:
//                system1:
//                    databases:
//                        db11: ~
//                        db12: ~
//                system2:
//                    databases:
//                        db21: ~
//                        db22: ~
        //-------------------------
        //This will generate two services, with the names <system1>_connection and <system2>_connection. This services inherit from MultipleDbConnectionClass.
        $bundle_path = 'DesarrolloHosting\MultipleDbConnectionBundle\Model\\';
        $other_path =  'AppBundle\Model\MultipleDbConnection\\';
        $look_paths = array($other_path, $bundle_path);
        
        foreach($config['services'] as $service_name => $service_parameters){
            $container->setParameter("services.$service_name.databases", $config['services'][$service_name]['databases']);
            
            $service_class_name = ucfirst($service_name).'Connection';
            
            $real_path = '';
            $found_class = false;
            foreach($look_paths as $look_path){
                if(class_exists($look_path.$service_class_name)){
                    $found_class = true;
                    $real_path = $look_path;
                    break;
                }
            }
            if(!$found_class){
                $real_path = $bundle_path;
                $service_class_name = "MultipleDbConnection";
                
            }
            
            $definition = new Definition(
                    $real_path.$service_class_name,
                    array($service_name, '%services.'.$service_name.'.databases%')
            );
//            $definition->addTag('monolog.logger');
            $container->setDefinition($service_name.'_connection', $definition);
        }
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
    
    public function getAlias() {
        return 'multiple_db_connection';
    }
}