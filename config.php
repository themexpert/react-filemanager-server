<?php

return [
	'root' => realpath(__DIR__ . '/../react-filemanager-server/public/'),
    'upload' => [
        'allowed_types' => ['image/jpeg',  'image/png', 'image/svg']
    ]
];