<?php
namespace App\Classes;

//delete all items in cart 
class Drop
{
    public function deletecart(): bool
    {
        setcookie("cart", "", time() - 3600, "/", false, true);
        return true;
    }
}
