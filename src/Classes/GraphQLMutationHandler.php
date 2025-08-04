<?php
namespace App\GraphQL;

use App\Classes\Connect; // your DB class

class GraphQLMutationHandler
{
    public static function saveCartToDatabase()
    {
        try {
            file_put_contents("graphql.log", "1. Starting raw insert test\n", FILE_APPEND);

            $cartItems = json_decode($_COOKIE['cart'] ?? '[]', true);

            if (!$cartItems || !is_array($cartItems)) {
                throw new \Exception("Cart is empty or not valid JSON");
            }

            $conn = new Connect(); 

            foreach ($cartItems as $item) {
                $data = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'price' => $item['amount'],
                    'quantity' => $item['quantity'],
                    'category' => $item['category'],
                ];

                file_put_contents("graphql.log", "Inserting: " . json_encode($data) . "\n", FILE_APPEND);
                $conn->setOrder($data); 
            }

            return true;

        } catch (\Throwable $e) {
            file_put_contents("graphql.log", "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            throw new \Exception("Internal error while saving cart");
        }
    }
}
