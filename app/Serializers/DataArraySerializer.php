<?php

namespace App\Serializers;

use League\Fractal\Serializer\ArraySerializer;

class DataArraySerializer extends ArraySerializer
{
    public function collection($resourceKey, array $data)
    {
        if ($resourceKey === null) {
            return ['data' => $data];
        }
        return [$resourceKey => $data];
    }

    public function item($resourceKey, array $data)
    {
        if ($resourceKey === null) {
            return ['data' => $data];
        }
        return [$resourceKey => $data];
    }
}