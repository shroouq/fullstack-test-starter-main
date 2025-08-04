<?php
namespace App\Classes;

//class decreass to decress quantity in cookies
class Decrease
{
    public function decrquan($id, $attributes): bool
    {

        if (!isset($_COOKIE["cart"]))
            return false;

        $cookie_data = stripslashes($_COOKIE["cart"]);
        $cart_data = json_decode($cookie_data, true);

        $found = false;

        foreach ($cart_data as $key => $item) {
            if (
                $item['id'] === $id &&
                $this->attributesMatch($item['attributes'] ?? [], $attributes ?? [])
            ) {
                $cart_data[$key]['quantity'] -= 1;
                $found = true;
                break;
            }
        }

        if ($found) {
            $updated = json_encode($cart_data);
            return setcookie("cart", $updated, time() + (86400 * 30), "/", false, true);
        }

        return false;
    }
//function to check the attribute and its value 
    private function attributesMatch(array $a, array $b): bool
    {
        if (count($a) !== count($b))
            return false;

        foreach ($a as $attrA) {
            $matched = false;
            foreach ($b as $attrB) {
                if (
                    $attrA['attribute_name'] === $attrB['attribute_name'] &&
                    $attrA['value'] === $attrB['value']
                ) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched)
                return false;
        }

        return true;
    }
}
