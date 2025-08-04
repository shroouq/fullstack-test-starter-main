<?php
namespace App\Classes;

class TouchID implements attributetype
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
        $this->data['touch_id'] = $this->value;
        return $this->data;
    }

}