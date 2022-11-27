<?php

declare(strict_types=1);

return [
    // Module config
    'blog' => new class
    {
        public function getViewDir(): string
        {
            return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'module'.DIRECTORY_SEPARATOR.'blog'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR;
        }

        public function getTemplateFileName(): string
        {
            return 'template';
        }
    },
];
