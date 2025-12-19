<?php

$host = "127.0.0.1";
$port = "3307";
$dbname = "realestate";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "<h2>DB Connection failed</h2><pre>".htmlspecialchars($e->getMessage())."</pre>";
    exit;
}

session_start();


function calculateDiscountedPrice(array $property): array
{
    $createdTime = strtotime($property['created_at']);
    $oneMonthAgo = strtotime('-1 month');
    
    $currentPrice = (int)($property['price'] ?? 0);
    $originalPrice = (int)($property['original_price'] ?? $currentPrice);
    
    $isDiscounted = false;
    $finalPrice = $currentPrice;
    
    if ($createdTime < $oneMonthAgo) {
        
        $discountedPrice = $originalPrice * 0.80;
        $roundedPrice = floor($discountedPrice / 100) * 100;
        $calculatedPrice = (int)$roundedPrice;

        if ($calculatedPrice < $originalPrice) {
            $finalPrice = $calculatedPrice;
            $isDiscounted = true;
        }
    }
    
    if (!$isDiscounted) {
        $finalPrice = $currentPrice;
        $originalPrice = 0;
    }

    return [
        'final_price' => $finalPrice,
        'original_price' => $originalPrice,
        'is_discounted' => $isDiscounted
    ];
}
?>