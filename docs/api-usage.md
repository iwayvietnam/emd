### API Usage
* Define base api url & create http client:
```php
define('BASE_API_URL', 'http://api.example.com');
$jar = new \GuzzleHttp\Cookie\CookieJar();
$client = new \GuzzleHttp\Client([
    'base_uri' => BASE_API_URL,
    'cookies' => $jar,
]);
```

* Authentication (obtain access token):
```php
$client->get(
    'login/csrf-cookie'
);
$xsrfToken = $jar->getCookieByName('XSRF-TOKEN')->getValue();
$response = $client->post(
    'login/token', [
        'headers' => [
            'X-XSRF-TOKEN' => urldecode($xsrfToken),
        ],
        'form_params' => [
            'email' => $email,
            'password' => $password,
            'device' => $device,
        ],
    ]
);
$accessToken = json_decode($response->getBody());
```

* Files uploading (for attachment)
```php
$response = $client->post(
    'api/upload', [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken->token,
            'Accept' => 'application/json',
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
            'Authorization' => 'Bearer ' . $accessToken->token,
            'Accept' => 'application/json',
        ],
        'form_params' => [
            'recipients' => [
                $email01,
                $email02,
            ],
            'headers' => [
                'Header-01: Value 01',
                'Header-02: Value 02',
            ],
            'message_id' => $message_id,
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
            'Authorization' => 'Bearer ' . $accessToken->token,
            'Accept' => 'application/json',
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
            'Authorization' => 'Bearer ' . $accessToken->token,
            'Accept' => 'application/json',
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
            'Authorization' => 'Bearer ' . $accessToken->token,
            'Accept' => 'application/json',
        ],
    ]
);
$devices = json_decode($response->getBody());
```

### Rate Limiting
* Authentication restrict the amount of auth token creating for a given email address by 1 request per hour
* API restrict the amount of traffic for a given user by 60 request per minute
