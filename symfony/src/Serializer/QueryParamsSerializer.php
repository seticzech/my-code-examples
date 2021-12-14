<?php

namespace App\Serializer;

use Symfony\Component\HttpFoundation\Request;

class QueryParamsSerializer
{

    public function deserialize(Request $request, array $keys = [])
    {
        return !empty($keys)
            ? array_filter($request->query->all(), function ($key) use ($keys) {
                return in_array($key, $keys);
            }, ARRAY_FILTER_USE_KEY)
            : $request->query->all();
    }

}
