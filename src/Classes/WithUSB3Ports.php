<?php
namespace App\Classes;

class WithUSB3Ports implements attributetype
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
        $this->data['with_usb_3_ports'] = $this->value;
        return $this->data;
    }
}
