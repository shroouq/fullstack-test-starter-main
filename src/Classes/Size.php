<?php
namespace App\Classes;

//class size to set attribute value 
class Size implements \App\Classes\attributesort
{
    private $data;
    private $value;

    public function __construct(array $data, $value)
    {
        $this->data = $data;
        $this->value = $value;
    }

    public function clothesattribute()
    {
        $this->data['Size'] = $this->value;
        return $this->data;
    }
}
