<?php

namespace App\Entity;

use App\Entity\Exception\ImmutableException;


trait Immutable
{

    public function __call($name, $arguments)
    {
        $operation = substr($name, 0, 3);
        $property = lcfirst(substr($name, 3));

        $property = $this->normalize($property);

        if (($operation === 'set') && property_exists($this, $property)) {
            throw new ImmutableException(sprintf('Value for property \'%s\' in class %s cannot be set via setter, pass the dependencies in constructor instead.', $property, self::class));
        } elseif (($operation === 'get') && property_exists($this, $property)) {
            return $this->{$property};
        } elseif (class_parents($this)) {
            parent::__call($name, $arguments);
        }
    }



    protected function normalize($value)
    {
        if (false !== strpos($value, '_')) {
            $e = explode('_', $value);
            array_walk($e, function($val, $key) {
                if ($key > 0) {
                    $val = ucfirst(strtolower($val));
                }
                return $val;
            });

            $value = implode('', $e);
        }

        return $value;
    }



    public function setValues($values)
    {
        if ($values instanceof \Traversable) {
            $values = iterator_to_array($values);
        } elseif (!is_array($values)) {
            throw new ImmutableException(sprintf('First parameter must be an array, %s given.', gettype($values)));
        }

        foreach ($values as $key => $val) {
            $method = 'set' . ucfirst($this->normalize($key));
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $val);
            }
        }
    }

}
