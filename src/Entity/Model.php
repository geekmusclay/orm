<?php

declare(strict_types=1);

namespace Geekmusclay\ORM\Entity;

use Geekmusclay\ORM\Common\AbstractEntity;

use function preg_replace;
use function strtolower;

class Model extends AbstractEntity
{
    /** @var string|null $table The name of the table represented by the entity */
    protected ?string $table = null;

    /** @var string $primary The name of the primary key of the table represented by the entity */
    protected ?string $primary = 'id';

    /** @var string[] $serializeExclude Property listed below wont appear in serialization */
    protected array $serializeExclude = [
        'serializeExclude',
        'table',
        'primary'
    ];

    /**
     * Determines the name of the target table based on the class name
     *
     * @return string The name of the target table
     */
    public function getTable(): string
    {
        if (null === $this->table) {
            $class = explode('\\', static::class);
            $name = preg_replace('/(?<!^)[A-Z]/', '_$0', $class[ count($class) - 1 ]);

            return strtolower($name) . 's';
        }

        return $this->table;
    }
}
