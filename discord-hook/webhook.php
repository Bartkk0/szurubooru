<?php

$BOORU_URL = getenv("DISCORD_BOORU_URL");
$WEBHOOK_URL = getenv("DISCORD_WEBHOOK_URL");
$TOKEN = getenv("DISCORD_USER_TOKEN");

$data = file_get_contents('php://input');
$data = json_decode($data);

$id = $data->id;

$ch = curl_init("$BOORU_URL/api/post/$id");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
    "Authorization: Token $TOKEN"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$api_data = curl_exec($ch);
$api_data = json_decode($api_data);
curl_close($ch);

$thumbnail_url = $BOORU_URL . "/" . $api_data->thumbnailUrl;

$author = [];
if($api_data->user == null){
    $author = [
        'name' => 'Anonymous'
    ];
} else {
    $author = [
        'name' => $api_data->user->name,
        'icon_url' => "$BOORU_URL/{$api_data->user->avatarUrl}"
    ];
}


$embed = [
    'embeds' => [
        [
            'title' => 'Uploaded a new post',
            'url' => "$BOORU_URL/post/$data->id",
            'timestamp' => $api_data->creationTime,
            'author' => $author,
            'image' => [
                'url' => "$BOORU_URL/$api_data->thumbnailUrl"
            ]
        ]
    ]
];

//error_log(print_r(json_encode($embed),true));

$ch = curl_init($WEBHOOK_URL);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($embed));
curl_exec($ch);
curl_close($ch);