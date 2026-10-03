<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Aktarım anahtarı
    |--------------------------------------------------------------------------
    |
    | false iken admin paneldeki "GitHub'a Gönder" düğmesi görünür ama
    | çalışmaz. Token ve depo hazır olunca .env içinde DEPLOY_ENABLED=true yapın.
    |
    */
    'enabled' => (bool) env('DEPLOY_ENABLED', false),

    'repository' => env('DEPLOY_GITHUB_REPOSITORY'),

    'token' => env('DEPLOY_GITHUB_TOKEN'),

    'branch' => env('DEPLOY_GITHUB_BRANCH', 'main'),

    'mysqldump' => env('DEPLOY_MYSQLDUMP', 'C:\\xampp\\mysql\\bin\\mysqldump.exe'),

    /*
    | true iken ürün / kategori / sayfa / menü / ayar tabloları
    | database/deploy/catalog.sql olarak paketlenir.
    | Müşteri, sipariş ve destek tabloları asla dahil edilmez.
    */
    'include_catalog' => (bool) env('DEPLOY_INCLUDE_CATALOG', true),

    /*
    |--------------------------------------------------------------------------
    | Canlıya gidebilecek yollar
    |--------------------------------------------------------------------------
    |
    | Yalnızca bu listede olan ve gerçekten değişmiş dosyalar stage edilir.
    | vendor, node_modules, .env, log ve geçici dosyalar buraya yazılmaz.
    |
    */
    'include_paths' => [
        'app',
        'bootstrap/app.php',
        'bootstrap/cache/.gitignore',
        'config',
        'database/migrations',
        'database/deploy',
        'database/seeders',
        'public/css',
        'public/js',
        'public/images',
        'public/index.php',
        'public/.htaccess',
        'public/favicon.ico',
        'resources',
        'routes',
        'storage/app/.gitignore',
        'storage/app/public',
        'storage/framework/.gitignore',
        'storage/framework/cache/.gitignore',
        'storage/framework/cache/data/.gitignore',
        'storage/framework/sessions/.gitignore',
        'storage/framework/testing/.gitignore',
        'storage/framework/views/.gitignore',
        'storage/logs/.gitignore',
        '.github',
        '.gitignore',
        '.env.example',
        'artisan',
        'composer.json',
        'composer.lock',
        'package.json',
        'webpack.mix.js',
    ],

    /*
    | Allowlist içinde kalsa bile asla gönderilmeyecek yollar.
    | Not: bootstrap/cache ve storage/framework bütünüyle engellenmez;
    | yalnızca yukarıdaki .gitignore iskelet dosyaları pakete girer.
    */
    'exclude_paths' => [
        '.env',
        '.env.backup',
        '.env.local',
        '.env.production',
        '.env.staging',
        'node_modules',
        'vendor',
        'tests',
        'storage/app/deploy-mysql.cnf',
        'public/storage',
        'public/hot',
    ],

    /*
    | Dosya / klasör adı bu kalıplara uyarsa canlı pakete girmez.
    | (macOS AppleDouble, sistem çöpleri vb.)
    */
    'exclude_name_patterns' => [
        '._*',
        '.DS_Store',
        '__MACOSX',
        'Thumbs.db',
        'ehthumbs.db',
        'Desktop.ini',
        '*~',
        '*.tmp',
        '*.temp',
        '*.swp',
        '*.swo',
        '*.bak',
        '*.old',
        '*.orig',
        '*.log',
    ],

    /*
    | Önizleme / gönderim öncesi bu dosyaların pakete girebilir olması gerekir.
    | Fresh clone sonrası Laravel yazılabilir dizin iskeleti için şarttır.
    */
    'required_structure_files' => [
        'bootstrap/cache/.gitignore',
        'storage/app/.gitignore',
        'storage/app/public/.gitignore',
        'storage/framework/.gitignore',
        'storage/framework/cache/.gitignore',
        'storage/framework/sessions/.gitignore',
        'storage/framework/views/.gitignore',
        'storage/logs/.gitignore',
        'artisan',
        'composer.json',
        'composer.lock',
        'public/index.php',
        '.env.example',
        '.github/workflows/deploy.yml',
    ],

    /*
    | Canlıdaki müşteri hareketi bu tablolarda durur ve yerel aktarım onları silmez.
    */
    'preserve_tables' => [
        'users',
        'password_resets',
        'customers',
        'customer_favorites',
        'customer_order_requests',
        'reservations',
        'support_tickets',
        'support_messages',
        'messages',
        'integrations',
        'sessions',
        'cache',
        'jobs',
        'failed_jobs',
        'personal_access_tokens',
        'migrations',
    ],

];
