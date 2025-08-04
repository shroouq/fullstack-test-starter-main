<?php
namespace App\Classes;

class SetCookies
{
    //save cart in cookies 
    public function saveProductToCookie(array $product): bool
    {
        if (isset($_COOKIE["cart"])) {
            $cookie_data = stripslashes($_COOKIE['cart']);
            $cart_data = json_decode($cookie_data, true);
        } else {
            $cart_data = [];
        }

        $product['quantity'] = $product['quantity'] ?? 1;
        $found = false;

        foreach ($cart_data as $key => $item) {
            if (
                $item['id'] === $product['id'] &&
                $this->attributesMatch($item['attributes'] ?? [], $product['attributes'] ?? [])
            ) {
                $cart_data[$key]['quantity'] += $product['quantity'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart_data[] = $product;
        }

        $item_data = json_encode($cart_data);

        return setcookie("cart", $item_data, time() + (86400 * 30), "/", "localhost", false, true);
    }
    //function to ceck if the attribute value exict or not 
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
