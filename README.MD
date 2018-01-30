## React File Manager Server

## Dependency
https://github.com/themexpert/react-filemanager

## Usage

`composer require i-rocky/react-filemanager-server`

Let's say we have the following file structure

* $_SERVER['DOCUMENT_ROOT']
    * app
        * config.php
        * index.php
    * public
        * uploads

File: `config.php`
```PHP
<?php

return [
	'root' => '/public/storage/', //your public upload directory
    'upload' => [
        'allowed_types' => ['image/jpeg',  'image/png']
    ]
];
```

File: `/app/index.php` 
```PHP
    (new FileManager(__DIR__ . '/config.php'));
    exit; //just incase someone else tries to send some more data or maybe upto you
```

Now our server endpoint will be `/app/`

We can use any other file and have the server endpoint wherever we want