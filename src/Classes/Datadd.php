<?php
namespace App\Classes;

use App\Classes\Clothes;

interface productType
{
    public function addproducb();
}

class Datadd
{
    public function addproduc(array $cart)
    {
        foreach ($cart as $item) {
            $class = 'App\\Classes\\' . ucfirst($item['category']);

            if (!class_exists($class)) {
                throw new \Exception("Class $class not found.");
            }

            $handler = new $class($item);

            if (!method_exists($handler, 'addproducb')) {
                throw new \Exception("Method addproducb() not found in $class.");
            }

            $handler->addproducb(); 
        }

        return true; 
    }
}
