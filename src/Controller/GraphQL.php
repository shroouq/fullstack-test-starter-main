<?php
namespace App\Controller;

use App\Classes\setCookies;
use App\Classes\Datadd;
use App\Classes\Increase;
use App\Classes\Decrease;
use App\Classes\Delete;
use App\Classes\Drop;

use App\Classes\Database;
use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use RuntimeException;
use Throwable;
error_reporting(E_ALL);
ini_set('display_errors', 1);
class GraphQL
{
    public static function handle()
    {
        try {
            // Instantiate Database and connect
            $db = new Database();
            $pdo = $db->connect();


            $attributeInputType = new InputObjectType([
                'name' => 'AttributeInput',
                'fields' => [
                    'attribute_name' => Type::string(),
                    'value' => Type::string(),
                ],
            ]);


            $attributeItemType = new ObjectType([
                'name' => 'AttributeItem',
                'fields' => [
                    'id' => Type::string(),
                    'value' => Type::string(),
                ]
            ]);
            $categoryType = new ObjectType([
                'name' => 'Category',
                "fields" => [
                    'name' => Type::string(),
                ],
            ]);

            $attributeType = new ObjectType([
                'name' => 'Attribute',
                'fields' => [
                    'name' => Type::string(),
                    'items' => Type::listOf($attributeItemType),
                ]
            ]);
            $cookiesAttributeType = new ObjectType([
                'name' => 'SelectedAttribute',
                'fields' => [
                    'attribute_name' => Type::string(),
                    'value' => Type::string(),
                ]
            ]);
            $priceType = new ObjectType([
                'name' => 'Price',
                'fields' => [
                    'amount' => Type::float(),
                    'currencysymbol' => Type::string(),
                ]
            ]);
            $gallerType = new ObjectType([
                'name' => 'Gallery',
                'fields' => [
                    'pic' => Type::string(),
                ]
            ]);


            // Define ProductInputType
            $productInputType = new InputObjectType([
                'name' => 'ProductInput',
                'fields' => [
                    'id' => Type::string(),
                    'name' => Type::string(),
                    'inStock' => Type::string(),
                    'category' => Type::string(),
                    'amount' => Type::float(),
                    'pic' => Type::string(),
                    'attributes' => Type::listOf($attributeInputType),
                ],
            ]);

            // Define Product Type (simplified for name, price, image)
            $productType = new ObjectType([
                'name' => 'Product',
                'fields' => [
                    'id' => Type::string(),
                    'name' => Type::string(),
                    'inStock' => Type::string(),
                    'category' => Type::string(),
                    'description' => Type::string(),
                    'brand' => Type::string(),
                    'image' => Type::string(), // shortcut
                    'gallery' => Type::listOf($gallerType),
                    'price' => $priceType,
                    'attributes' => Type::listOf($attributeType),
                ],
            ]);


            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'products' => [
                        'type' => Type::listOf($productType),
                        'resolve' => function () use ($pdo) {
                            try {
                                $stmt = $pdo->query("
                                    SELECT 
                                    p.id,
                                  p.name,
                                   p.inStock,
                        p.category,
                               (
                                    SELECT amount 
                            FROM prices 
                               WHERE prices.productid = p.id 
                                    LIMIT 1
                                ) AS amount,
                                  (
                              SELECT currencysymbol 
                        FROM prices 
                      WHERE prices.productid = p.id 
                        LIMIT 1
                    ) AS currencysymbol,
                           (
                       SELECT pic 
                         FROM gallery 
                       WHERE gallery.product_id = p.id 
                       LIMIT 1
                      ) AS image
                       FROM products p;

            ");
                                $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                                // set price 
                                foreach ($products as &$product) {
                                    $product['price'] = [
                                        'amount' => (float) $product['amount'],
                                        'currencysymbol' => $product['currencysymbol'],
                                    ];
                                    unset($product['amount'], $product['currencysymbol']);
                                }
                                return $products;
                            } catch (\Throwable $e) {
                                throw new \Exception("Database error: " . $e->getMessage());
                            }
                        },
                    ],
                    //get category 
                    'getcategory' => [
                        'type' => Type::listOf($categoryType),
                        'resolve' => function () use ($pdo) {
                            try {
                                $stmt = $pdo->query("
                        SELECT name FROM  categories
                        ");
                                $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                                return $categories;
                            } catch (\Throwable $e) {
                                throw new \Exception("Database error: " . $e->getMessage());
                            }
                        }
                    ],

                    // get product from database 
                    'product' => [
                        'type' => $productType,
                        'args' => [
                            'id' => Type::nonNull(Type::string()),
                        ],
                        'resolve' => function ($root, $args) use ($pdo) {

                            $id = $args['id'];

                            //  Fetch product
                            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
                            $stmt->execute([$id]);
                            $product = $stmt->fetch(\PDO::FETCH_ASSOC);
                            if (!$product)
                                return null;

                            //  Fetch price
                            $priceStmt = $pdo->prepare("SELECT amount, currencysymbol FROM prices WHERE productid = ? LIMIT 1");
                            $priceStmt->execute([$id]);
                            $product['price'] = $priceStmt->fetch(\PDO::FETCH_ASSOC);

                            // Fetch all gallery images
                            $imgStmt = $pdo->prepare("SELECT pic FROM gallery WHERE product_id = ?");
                            $imgStmt->execute([$id]);
                            $gallery = $imgStmt->fetchAll(\PDO::FETCH_ASSOC);
                            $product['gallery'] = $gallery;
                            $product['image'] = $gallery[0]['pic'] ?? null; // set first image
            
                            //  Fetch attributes and group by name
                            $attrStmt = $pdo->prepare("
               
                    SELECT a.name, ai.id AS item_id, ai.value
                    FROM attribute_items ai
                    JOIN attributes a ON a.id = ai.attributeid
                    WHERE ai.productid = ?
                ");
                            $attrStmt->execute([$id]);
                            $rawAttrs = $attrStmt->fetchAll(\PDO::FETCH_ASSOC);

                            $grouped = [];

                            foreach ($rawAttrs as $attr) {
                                $name = $attr['name'];
                                $itemId = $attr['item_id'];
                                $value = $attr['value'];

                                if (!isset($grouped[$name])) {
                                    $grouped[$name] = [
                                        'name' => $name,
                                        'items' => []
                                    ];
                                }

                                // Skip if this item ID already exists
                                $alreadyExists = false;
                                foreach ($grouped[$name]['items'] as $existing) {
                                    if ($existing['id'] === $itemId) {
                                        $alreadyExists = true;
                                        break;
                                    }
                                }

                                if (!$alreadyExists) {
                                    $grouped[$name]['items'][] = [
                                        'id' => $itemId,
                                        'value' => $value
                                    ];
                                }
                            }

                            $product['attributes'] = array_values($grouped);

                            return $product;
                        }
                    ],

                    //get product by category 
                    'productsByCategory' => [
                        'type' => Type::listOf($productType),
                        'args' => [
                            'category' => Type::nonNull(Type::string()),
                        ],
                        'resolve' => function ($root, $args) use ($pdo) {
                            $category = $args['category'];

                            try {
                                $stmt = $pdo->prepare("
                SELECT 
                    p.id,
                    p.name,
                    p.inStock,
                    p.category,
                    (
                        SELECT amount 
                        FROM prices 
                        WHERE prices.productid = p.id 
                        LIMIT 1
                    ) AS amount,
                    (
                        SELECT currencysymbol 
                        FROM prices 
                        WHERE prices.productid = p.id 
                        LIMIT 1
                    ) AS currencysymbol,
                    (
                        SELECT pic 
                        FROM gallery 
                        WHERE gallery.product_id = p.id 
                        LIMIT 1
                    ) AS image
                FROM products p
                WHERE p.category = ?
            ");
                                $stmt->execute([$category]);
                                $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                                foreach ($products as &$product) {
                                    $product['price'] = [
                                        'amount' => isset($product['amount']) ? (float) $product['amount'] : 0.0,
                                        'currencysymbol' => $product['currencysymbol'] ?? '$',
                                    ];
                                    unset($product['amount'], $product['currencysymbol']);
                                }

                                return $products;

                            } catch (\Throwable $e) {
                                throw new \Exception("Database error: " . $e->getMessage());
                            }
                        },
                    ],
                    //get product by id 
                    'ProductsByIds' => [
                        'type' => Type::listOf(new ObjectType([
                            'name' => 'ProductAttributesOnly',
                            'fields' => [
                                'id' => Type::string(),
                                'attributes' => Type::listOf($attributeType),
                            ]
                        ])),
                        'args' => [
                            'ids' => Type::listOf(Type::string())
                        ],
                        'resolve' => function ($root, $args) use ($pdo) {
                            $results = [];

                            foreach ($args['ids'] as $productId) {
                                $stmt = $pdo->prepare("
                SELECT 
                    a.name AS attribute_name,
                    ai.id AS item_id,
                    ai.value
                FROM attribute_items ai
                JOIN attributes a ON a.id = ai.attributeid
                WHERE ai.productid = ?
            ");
                                $stmt->execute([$productId]);
                                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                                $grouped = [];

                                foreach ($rows as $row) {
                                    $attrName = $row['attribute_name'];
                                    $itemId = $row['item_id'];

                                    if (!isset($grouped[$attrName])) {
                                        $grouped[$attrName] = [
                                            'name' => $attrName,
                                            'items' => []
                                        ];
                                    }

                                    // skip if item already exists
                                    $alreadyAdded = false;
                                    foreach ($grouped[$attrName]['items'] as $existingItem) {
                                        if ($existingItem['id'] === $itemId) {
                                            $alreadyAdded = true;
                                            break;
                                        }
                                    }

                                    if (!$alreadyAdded) {
                                        $grouped[$attrName]['items'][] = [
                                            'id' => $itemId,
                                            'value' => $row['value']
                                        ];
                                    }
                                }

                                $results[] = [
                                    'id' => $productId,
                                    'attributes' => array_values($grouped)
                                ];
                            }

                            return $results;
                        }
                    ],


                    //get cart from cookies
                    'getCartFromCookie' => [
                        'type' => Type::listOf(
                            new ObjectType([
                                'name' => 'CartItem',
                                'fields' => [
                                    'id' => Type::string(),
                                    'name' => Type::string(),
                                    'category' => Type::string(),
                                    'price' => Type::float(),
                                    'image' => Type::string(),
                                    'quantity' => Type::int(),
                                    'attributes' => Type::listOf($cookiesAttributeType),

                                ]
                            ])
                        ),
                        'resolve' => function () {
                            $cartItems = json_decode($_COOKIE['cart'] ?? '[]', true);

                            $result = [];

                            foreach ($cartItems as $item) {
                                $Id = $item['id'];

                                $result[] = [
                                    'id' => $Id,
                                    'name' => $item['name'],
                                    'category' => $item['category'],
                                    'price' => (float) $item['amount'],
                                    'image' => $item['pic'],
                                    'quantity' => (int) $item['quantity'],
                                    'attributes' => $item['attributes'] ?? [],
                                ];
                            }
                            return $result;
                        }
                    ]
                ],
            ]);

            //mutationtype 
            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    //get product from database and save it in cookies
                    'saveProduct' => [
                        'type' => Type::boolean(),
                        'args' => [
                            'productId' => ['type' => Type::nonNull(Type::string())],
                        ],
                        'resolve' => function ($root, $args) use ($pdo) {
                            $productId = $args['productId'];

                            // Fetch product details
                            $stmt = $pdo->prepare("
        SELECT 
            p.id, p.name, p.category,
            (SELECT amount FROM prices WHERE prices.productid = p.id LIMIT 1) AS amount,
            (SELECT pic FROM gallery WHERE gallery.product_id = p.id LIMIT 1) AS pic
        FROM products p
        WHERE p.id = ?
    ");
                            $stmt->execute([$productId]);
                            $product = $stmt->fetch(\PDO::FETCH_ASSOC);

                            if (!$product) {
                                throw new \Exception("Product not found.");
                            }

                            // Fetch first value for each attribute
                            $attrStmt = $pdo->prepare("
                                 SELECT attribute_name, value FROM (
            SELECT 
                a.name AS attribute_name,
                ai.value,
                ROW_NUMBER() OVER (PARTITION BY ai.attributeid ORDER BY ai.id ASC) as rn
            FROM attribute_items ai
            JOIN attributes a ON a.id = ai.attributeid
            WHERE ai.productid = ?
        ) as ranked
        WHERE rn = 1
    ");
                            $attrStmt->execute([$productId]);
                            $attributes = $attrStmt->fetchAll(\PDO::FETCH_ASSOC);

                            $product['attributes'] = $attributes;

                            // Use SetCookies class to save product to cookies
                            $cookieSetter = new SetCookies();
                            return $cookieSetter->saveProductToCookie($product);
                        }


                    ],
                    //save product from user to cookies
                    'saveCookies' => [
                        'type' => Type::string(),
                        'args' => [
                            'input' => [
                                'type' => $productInputType,
                            ],
                        ],
                        'resolve' => function ($root, $args) {
                            file_put_contents(__DIR__ . "/setcookies_debug_entry.txt", "Mutation hit at: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

                            $product = $args['input'];
                            file_put_contents(__DIR__ . "/setcookies_log.txt", json_encode($product, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

                            $cookieSetter = new SetCookies();
                            return $cookieSetter->saveProductToCookie($product);
                        },



                    ],
                    //increase quantity
                    'updateCartQuantity' => [
                        'type' => Type::boolean(),
                        'args' => [
                            'productId' => Type::nonNull(Type::string()),
                            'attributes' => Type::nonNull(Type::listOf($attributeInputType)),
                        ],
                        'resolve' => function ($root, $args) {
                            $id = $args['productId'];
                            $attributes = $args['attributes'];

                            $add = new Increase();
                            return $add->addquan($id, $attributes);
                        }
                    ],
                    //decrease quantity
                    'decreaseQuantity' => [
                        'type' => Type::boolean(),
                        'args' => [
                            'productId' => Type::nonNull(Type::string()),
                            'attributes' => Type::nonNull(Type::listOf($attributeInputType)),
                        ],
                        'resolve' => function ($root, $args) {
                            $id = $args['productId'];
                            $attributes = $args['attributes'];

                            $add = new Decrease();
                            return $add->decrquan($id, $attributes);
                        }
                    ],
                    'deleteQuantity' => [
                        'type' => Type::boolean(),
                        'args' => [
                            'productId' => Type::nonNull(Type::string()),
                            'attributes' => Type::nonNull(Type::listOf($attributeInputType)),
                        ],
                        'resolve' => function ($root, $args) {
                            $id = $args['productId'];
                            $attributes = $args['attributes'];

                            $add = new Delete();
                            return $add->deletequan($id, $attributes);
                        }
                    ],


                    //save order to database
                    'saveCartToDatabase' => [
                        'type' => Type::boolean(),
                        'resolve' => function () {


                            $delete = new Drop();
                            $delete->deletecart();
                            return true;
                        }
                    ]
                ]
            ]);


            $schema = new Schema(
                (new SchemaConfig())
                    ->setQuery($queryType)
                    ->setMutation($mutationType)
            );

            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) {
                throw new RuntimeException('Failed to get php://input');
            }

            $input = json_decode($rawInput, true);
            $query = $input['query'] ?? null;
            $variables = $input['variables'] ?? null;

            if (!$query) {
                throw new RuntimeException('No GraphQL query provided.');
            }

            $rootValue = [];
            $result = GraphQLBase::executeQuery($schema, $query, $rootValue, null, $variables);
            $output = $result->toArray();
        } catch (Throwable $e) {
            $output = [
                'error' => [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
            ];
        }

        header('Content-Type: application/json; charset=UTF-8');

        if (isset($output['error'])) {
            http_response_code(500);
        }

        echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
