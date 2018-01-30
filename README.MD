This is a Ract based filemanager built for the [Quix Pagebuilder](https://www.themexpert.com/quix-pagebuilder) and open sourced by ThemeXpert.

If you want to integrate file manager to you project you need to install the File manger client first. Details instruction will be found here https://github.com/themexpert/react-filemanager

## Usage

`composer require themexpert/react-filemanager-server`

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
