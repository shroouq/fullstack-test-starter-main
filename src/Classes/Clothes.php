<?php
namespace App\Classes;

use App\Classes\Size;

//interface for category clothes 
interface attributesort
{
    public function clothesattribute();
}

//class clothes for category clothes 
class Clothes implements producttype
{
    private $item;

    public function __construct(array $item)
    {
        $this->item = $item;
    }

    public function addproducb()
    {

        $data = [
            'id' => $this->item['id'],
            'name' => $this->item['name'],
            'price' => $this->item['amount'],
            'quantity' => $this->item['quantity'],
            'category' => $this->item['category'],
        ];


        // Safely handle attributes
        foreach ($this->item['attributes'] as $attr) {
            $key = $attr['attribute_name'];
            $data[$key] = null;
        }

        foreach ($this->item['attributes'] as $attr) {
            $class = 'App\\Classes\\' . ($attr['attribute_name']);
            $value = $attr['value'];


            $d = new $class($data, $value);
            $data = $d->clothesattribute();

        }

        $addproduct = new Contr();
        $addproduct->addpro($data);
    }
}

// namespace App\Classes;
// use App\Classes\Size;


// interface attributesort
// {
//     public function clothesattribute();
// }

// class Clothes implements producttype
// {
//                 private $item;

//      public function __construct(array $item) {

//         $this->item = $item;
//     }
//     public function addproduc()
//     {

//          $data = [
//         'id' => $this->item['id'],
//         'name' => $this->item['name'],
//         'price' => $this->item['price'],
//         'quantity' => $this->item['quantity'],
//         'image' => $this->item['image'],
//         'category' => $this->item['category'],
//     ];

//       foreach ($this->item['attributes'] as $attr) {
//         $key = $attr['attribute_name'];
//         $data[$key] = null;
// }
//  foreach ($this->item['attributes'] as $attr) {
//                $class = 'App\\Classes\\' . $attr['attribute_name'];
//                 $value= $attr['value'];
//                 $d =  new $class($data, $value);
//                 $data = $d->clothesattribute();

//                $addproduct = new Contr();
//                $addproduct->addpro($data);
//  }
//     }
// }
