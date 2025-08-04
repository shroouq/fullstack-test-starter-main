<?php
namespace App\Classes;

use App\Classes\Color;
use App\Classes\Capacity;
use App\Classes\WithUSB3Ports;
use App\Classes\TouchID;

interface attributetype
{
    public function techattribute(): array;
}

//class to set category 
class Tech implements producttype
{
    private $item;
    private $data;

    public function __construct(array $item)
    {
        $this->item = $item;
    }

    public function addproducb()
    {
        $this->data = [
            'id' => $this->item['id'],
            'name' => $this->item['name'],
            'price' => $this->item['amount'],
            'quantity' => $this->item['quantity'],
            'category' => $this->item['category'],
        ];

        foreach ($this->item['attributes'] as $attr) {
            $class = 'App\\Classes\\' . $this->setName($attr['attribute_name']);
            $value = $attr['value'];

            if (class_exists($class)) {
                $d = new $class($this->data, $value);
                $this->data = $d->techattribute();
            } else {
                file_put_contents('debug.log', "Missing attribute class: $class\n", FILE_APPEND);
            }
        }

        $addproduct = new Contr();
        $addproduct->addpro($this->data);
    }


    private function setName($name): string
    {
        $map = [
            'With USB 3 ports' => 'WithUSB3Ports',
            'Touch ID in keyboard' => 'TouchID',
            // Add more if needed
        ];

        return $map[$name] ?? str_replace([' ', '-', '_'], '', ucwords(strtolower($name)));
    }
}
