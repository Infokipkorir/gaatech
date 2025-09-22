<?php
function get_ai_reply($user_message) {
    $api_key = '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;' // 🔁 OpenAI API key

    $ch = curl_init('https://api.openai.com/v1/chat/completions');

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key,
    ];

    $post_fields = json_encode([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful support assistant.'],
            ['role' => 'user', 'content' => $user_message],
        ],
        'temperature' => 0.7
    ]);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post_fields
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        return "Error: " . curl_error($ch);
    }

    curl_close($ch);

    $data = json_decode($response, true);

    // ✅ Log errors for debugging
    if ($http_code !== 200 || !isset($data['choices'][0]['message']['content'])) {
        file_put_contents('openai_error_log.txt', "Response:\n$response\n\n");
        return "Sorry, I couldn't process your request.";
    }

    return trim($data['choices'][0]['message']['content']);
}
