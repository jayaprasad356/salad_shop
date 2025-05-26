<?php

// Log webhook for safety (optional)
file_put_contents("webhook_log.txt", date('Y-m-d H:i:s') . " => " . file_get_contents("php://input") . "\n", FILE_APPEND);

// Get raw JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Check if it's a successful payment
if (
    isset($data['payload']['payment_link']['entity']['status']) &&
    $data['payload']['payment_link']['entity']['status'] === "paid"
) {
    $reference_id = $data['payload']['payment_link']['entity']['reference_id'];

    // Expected format: "user_id-coin_id"
    $parts = explode('-', $reference_id);
    $user_id = isset($parts[0]) ? $parts[0] : null;
    $coins_id = isset($parts[1]) ? $parts[1] : null;

    if ($user_id && $coins_id) {
        $api_url = 'https://himaapp.in/api/auth/add_coins';

        $post_data = [
            'user_id' => $user_id,
            'coins_id' => $coins_id
        ];

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_POST, true);
        $api_response = curl_exec($ch);
        curl_close($ch);

        echo "✅ Payment received. Coins credited for User ID: $user_id";
    } else {
        echo "⚠️ Invalid reference ID format.";
    }
} else {
    echo "❌ Payment not successful or not a valid Razorpay event.";
}
