<?php

declare(strict_types=1);

namespace Geekmusclay\ORM;

use Closure;
use Exception;

use function array_merge;
use function call_user_func_array;
use function count;
use function is_callable;
use function json_encode;
use function strrpos;
use function ucfirst;

/**
 * This class describes an entity.
 */
abstract class AbstractEntity
{
    /**
     * Constructs a new instance.
     *
     * @param  array   $arguments  The entity properties
     * @return void
     */
    public function __construct(array $arguments = [])
    {
        if (0 !== count($arguments)) {
            foreach ($arguments as $property => $argument) {
                $this->{$property} = $argument;
            }

            foreach ($arguments as $funcName => $value) {
                if (false === $value instanceof Closure) {
                    $this->{"set" . ucfirst($funcName)} = function ($stdObject, $value) use ($funcName) {
                        $stdObject->{$funcName} = $value;
                    };
                    $this->{"get" . ucfirst($funcName)} = function ($stdObject) use ($funcName) {
                        return $stdObject->{$funcName};
                    };
                }
            }
        }
    }

    /**
     * Call magic method.
     *
     * @param  string     $method     The method
     * @param  mixed      $arguments  The arguments
     * @throws Exception
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        // Note: method argument 0 will always referred to the main class ($this).
        $arguments = array_merge(["stdObject" => $this], $arguments);
        if (true === isset($this->{$method}) && true === is_callable($this->{$method})) {
            return call_user_func_array($this->{$method}, $arguments);
        } else {
            throw new Exception("Fatal error: Call to undefined method stdObject::{$method}()");
        }
    }

    /**
     * Returns a json representation of the object.
     *
     * @return  string  Json representation of the object.
     */
    public function toJson(): string
    {
        $data = [];
        foreach ($this as $property => $value) {
            if (false === strrpos($property, 'set') && false === strrpos($property, 'get')) {
                $data[ $property ] = $value;
            }
        }

        return json_encode($data);
    }
}
