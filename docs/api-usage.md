### API Usage
* Define base api url & create http client:
```php
define('API_BASE_URL', 'http://api.example.com');
$client = new \GuzzleHttp\Client([
    'base_uri' => API_BASE_URL,
]);
```

* Authentication (obtain access token):
```php
$response = $client->post(
    'oauth/token', [
        'form_params' => [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => $grantType,
            'username' => $username,
            'password' => $password,
            'scope' => $scope,
        ],
    ]
);
$token = json_decode((string) $response->getBody());
$accessToken = $token->access_token;
$refresh_token = $token->refresh_token;
```

* Files uploading (for attachment)
```php
$response = $client->post(
    'api/upload', [
        'headers' => [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->access_token,
        ],
        'multipart' => [
            [
                'name' => 'name-01',
                'contents' => fopen('path-to-the-file', 'rb'),
                'filename' => 'filename-01',
            ],
            [
                'name' => 'name-02',
                'contents' => fopen('path-to-the-file', 'rb'),
                'filename' => 'filename-02',
            ],
        ],
    ]
);
$uploads = json_decode($response->getBody());
```

* Message sending
```php
$response = $client->post(
    'api/send', [
        'headers' => [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->access_token,
        ],
        'form_params' => [
            'recipient' $recipient,
            'headers' => [
                'Header-01: Value 01',
                'Header-02: Value 02',
            ],
            'message_id' => $messageId,
            'subject' => $subject,
            'content' => $content,
            'uploads' => $uploads,
        ],
    ]
);
$message = json_decode($response->getBody());
```

* Message listing
```php
$response = $client->get(
    'api/email', [
        'headers' => [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->access_token,
        ],
    ]
);
$messages = json_decode($response->getBody());
```

* Message viewing
```php
$response = $client->get(
    'api/email/1', [
        'headers' => [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->access_token,
        ],
    ]
);
$message = json_decode($response->getBody());
```

* Message tracking device listing
```php
$response = $client->get(
    'api/email/1/devices', [
        'headers' => [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->access_token,
        ],
    ]
);
$devices = json_decode($response->getBody());
```

### Rate Limiting
* Authentication restrict the amount of auth token creating for a given email address by 1 request per hour
* API restrict the amount of traffic for a given user by 60 request per minute
