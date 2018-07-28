<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    |
    | Configure how attached file information in stored on attachables.
    |
    */

    'model' => [

        // Mark which columns should be filled on the model by default.
        // These attributes are prefixed by <attribute name>_.
        'attributes' => [
            'size'         => true,
            'content_type' => true,
            'updated_at'   => true,
            'created_at'   => false,
            // JSON information about stored variants.
            'variants'     => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Settings for handling storage of uploaded files.
    |
    */

    'storage' => [

        // The Laravel storage disk to use.
        'disk' => 'public', //'paperclip',

        // Per disk, the base URL where attachments are stored at
        'base-urls' => [
            'public' => config('app.url') . '/storage',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Path sudo ln -s /scratch scratch
    |--------------------------------------------------------------------------
    |
    | The storage path that uploaded files and variants for models are placed in.
    |
    */

    'path' => [

        // The class that generates the paths
        'interpolator' => \Czim\Paperclip\Path\Interpolator::class,

        // The path to the original file to be interpolated. This will also\
        // be used for variant paths if the variant key is unset.
        //'original' => ':class/:attachment/:id_partition/:attribute/:variant/:filename',
        'original' => '/paperclip/:class/:attachment/:id_partition/:variant/:filename',

        // If the structure for variant filenames should differ from the
        // original, it may be defined here.
        'variant'  => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Variants
    |--------------------------------------------------------------------------
    |
    | Processed files may have any number of variants: versions of the file that
    | are resized, rotated, compressed, or whatever you can think of.
    |
    */

    // The default to use for the main URL
    'default-variant' => 'original',

    // Variant processing configuration
    'variants' => [

        'aliases' => [
            'auto-orient' => \Czim\FileHandling\Variant\Strategies\ImageAutoOrientStrategy::class,
            'resize'      => \Czim\FileHandling\Variant\Strategies\ImageResizeStrategy::class,
        ],

        // If no specific variants are set for a clipped file on a Model, these
        // variant definitions will be used.
        'default' => [

            // 'variantname' => [
            //     'strategy-alias' => [ 'strategy' => 'configuration' ],
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Preservation
    |--------------------------------------------------------------------------
    |
    | Settings to affect when attachment files are destroyed.
    |
    */

    // Set this to true in order to prevent older file uploads from being deleted.
    'keep-old-files' => false,

    // Set this to true in order to prevent file uploads from being deleted as attachments are destroyed.
    'preserve-files' => false,

    /*
    |--------------------------------------------------------------------------
    | Imagine
    |--------------------------------------------------------------------------
    |
    | The default binding to use for the ImagineInterface. May be Gd or Imagick.
    |
    */

    'imagine' => Imagine\Gd\Imagine::class,

    /*
    |--------------------------------------------------------------------------
    | Processing
    |--------------------------------------------------------------------------
    |
    | Settings for (re)processing attachments.
    |
    */

    'processing' => [
        'chunk-size' => 500,
    ],

];