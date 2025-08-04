<?php
namespace App\Classes;

//class capacity to set capacity attribute
class Capacity implements attributetype
{
    private $data;
    private $value;

    public function __construct(array $data, $value) {
        $this->data = $data;
        $this->value = $value;
    }

    public function techattribute(): array {
        $this->data['capacity'] = $this->value;
        return $this->data;
    }

}