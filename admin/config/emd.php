<?php

return [
    "app_domain" => env("APP_DOMAIN", "yourdomain.com"),
    "https" => (bool) env("FORCE_HTTPS", false),
    "panel" => [
        "id" => env("PANEL_ID", "admin"),
        "path" => env("PANEL_PATH", "admin"),
        "top_navigation" => (bool) env("TOP_NAVIGATION", false),
    ],
    "api" => [
        "hash_secret" => env("API_HASH_SECRET", true),
        "password_grant" => env("API_PASSWORD_GRANT", false),
        "request_rate" => (int) env("API_REQUEST_RATE", 600),
        "upload_dir" => env("API_UPLOAD_DIR", "attachments"),
        "acccess_tokens_expiry" => (int) env("ACCCESS_TOKENS_EXPIRY", 30),
        "refresh_tokens_expiry" => (int) env("REFRESH_TOKENS_EXPIRY", 180),
        "personal_tokens_expiry" => (int) env("PERSONAL_TOKENS_EXPIRY", 365),
    ],
    "mail" => [
        "queue_name" => env("MAIL_QUEUE_NAME", "default"),
        "should_queue" => (bool) env("MAIL_SHOULD_QUEUE", true),
        "track_click" => (bool) env("MAIL_TRACK_CLICK", false),
    ],
    "opendkim" => [
        "signing_table" => env(
            "OPENDKIM_SIGNING_TABLE", "/etc/opendkim/SigningTable"
        ),
        "key_table" => env(
            "OPENDKIM_KEY_TABLE", "/etc/postfix/KeyTable"
        ),
        "trusted_hosts" => env(
            "OPENDKIM_TRUSTED_HOSTS", "/etc/postfix/TrustedHosts"
        ),
    ],
    "policy" => [
        "listen_host" => env("POLICY_LISTEN_HOST", "127.0.0.1"),
        "listen_port" => env("POLICY_LISTEN_PORT", 1403),
        "server_worker" => env("POLICY_SERVER_WORKER", 4),
    ],
    "postfix" => [
        "client_ip_access" => env(
            "POSTFIX_CLIENT_IP_ACCESS", "/etc/postfix/client_ip_access"
        ),
        "sender_access" => env(
            "POSTFIX_SENDER_ACCESS", "/etc/postfix/sender_access"
        ),
        "recipient_restriction" => env(
            "POSTFIX_RECIPIENT_RESTRICTION", "/etc/postfix/recipient_restriction"
        ),
        "sender_transport" => env(
            "POSTFIX_SENDER_TRANSPORT", "/etc/postfix/sender_transport"
        ),
    ],
    "warning" => [
        "threshold" => (int) env("WARNING_THRESHOLD", 80),
        "subject" => env("WARNING_SUBJECT", "Policy Limit Warning"),
        "recipient" => env("WARNING_RECIPIENT"),
    ],
];
