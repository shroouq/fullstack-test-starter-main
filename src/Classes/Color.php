<?php
namespace App\Classes;

use App\Classes\attributetype;

//class color for attribute color
class Color implements attributetype
{
    private $data;
    private $value;

    public function __construct(array $data, $value)
    {
        $this->data = $data;
        $this->value = $value;
    }

    public function techattribute(): array
    {
        $this->data['color'] = $this->value;
        return $this->data;
    }
}
