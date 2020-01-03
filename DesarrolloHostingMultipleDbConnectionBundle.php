<?php
 
namespace DesarrolloHosting\MultipleDbConnectionBundle;
 
use Symfony\Component\HttpKernel\Bundle\Bundle;
use DesarrolloHosting\MultipleDbConnectionBundle\DependencyInjection\MultipleDbConnectionExtension;
 
class DesarrolloHostingMultipleDbConnectionBundle extends Bundle
{
    public function getContainerExtension() {
        return new MultipleDbConnectionExtension();
    }
}