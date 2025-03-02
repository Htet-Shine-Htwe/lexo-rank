<?php

namespace Dede\Lexorank;

use Dede\Lexorank\Services\LexoRankGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;

// 
trait LexoRankTrait
{

    /**
     * Adds position to model on creating event.
     */
    protected static function bootLexoRankTrait()
    {
        static::creating(
            function ($model) {
                /* @var Model $model */
                $sortableField = static::getSortableField();

                $query = static::applySortableQuery($model->newQuery(),$model);

                $max = $query->newQuery()->max($sortableField) ?: '`';

                $nextIndex  =  static::getNewPosition($max);

                $model->setAttribute($sortableField, $nextIndex);
            }
        );
    }
    
    /**
     * applySortableQuery
     *
     * @param  Builder $query
     * @param  Model $model
     * @return Builder
     */
    protected static function applySortableQuery(Builder $query,Model $model)
    {
        return $query;
    }

    /**
     * @param QueryBuilder $query
     *
     * @return QueryBuilder
     */
    public function scopeSorted($query)
    {
        $sortableField = static::getSortableField();

        return $query->orderBy($sortableField);
    }

    /**
     * Moves $this model after $entity model (and rearrange all entities).
     *
     * @param Model $entity
     *
     * @throws \Exception
     */
    public function moveAfter($entity)
    {
        $this->move('moveAfter', $entity);
    }

    /**
     * Moves $this model before $entity model (and rearrange all entities).
     *
     * @param Model $entity
     *
     * @throws \LexoRankException
     */
    public function moveBefore($entity)
    {
        $this->move('moveBefore', $entity);
    }

    /**
     * @param string $action moveAfter/moveBefore
     * @param Model  $entity
     *
     * @throws \LexoRankException
     */
    public function move($action, $entity)
    {
        $sortableField = static::getSortableField();
        $entityPosition = $entity->getAttribute($sortableField);


        if ($action === 'moveBefore') {
            $previous = optional($entity->previous()->first())->$sortableField;
            $next = $entityPosition;
        } else {
            $previous = $entityPosition;
            $next = optional($entity->next()->first())->$sortableField;
        }


        $this->_transaction(function () use ($sortableField, $previous, $next) {
            $this->setAttribute($sortableField, static::getNewPosition($previous, $next, true));
            $this->save();
        });
    }

    /**
     * @param string $prev
     * @param string $next
     * @return mixed
     */
    public static function getNewPosition(string $prev,string $next = '',bool $isMoving = false): string
    {
        return (new LexoRankGenerator((string)$prev, (string)$next))->get($isMoving);
    }

    /**
     * @param int $limit
     *
     * @return QueryBuilder
     */
    public function previous(int $limit = 0)
    {
        return $this->siblings(false, $limit);
    }

    /**
     * @param int $limit
     *
     * @return QueryBuilder
     */
    public function next(int $limit = 0)
    {
        return $this->siblings(true, $limit);
    }

    /**
     * @param bool $isNext is next, otherwise before
     * @param int  $limit
     *
     * @return QueryBuilder
     */
    public function siblings(bool $isNext,int $limit = 0)
    {
        $sortableField = static::getSortableField();

        $query = $this->newQuery();
        $query->where($sortableField, $isNext ? '>' : '<', $this->getAttribute($sortableField));
        $query->orderBy($sortableField, $isNext ? 'asc' : 'desc');
        if ($limit !== 0) {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * @param int $limit
     *
     * @return Collection|static[]
     */
    public function getPrevious($limit = 0)
    {
        /** @var Collection $collection */
        $collection = $this->previous($limit)->get();

        return $collection->reverse();
    }
    
    /**
     * getMiddle
     *
     * @param  string $prev
     * @param  string $next
     * @return string
     */
    public function getMiddle(string $prev,string $next)
    {
        return (new LexoRankGenerator((string)$prev, (string)$next))->get(true);
    }

    /**
     * @param int $limit
     *
     * @return Collection
     */
    public function getNext($limit = 0)
    {
        return $this->next($limit)->get();
    }

    /**
     * @param \Closure $callback
     *
     * @return mixed
     */
    protected function _transaction(\Closure $callback)
    {
        return $this->getConnection()->transaction($callback);
    }

    /**
     * @return string
     */
    public static function getSortableField()
    {
        $sortableField = isset(static::$sortableField) ? static::$sortableField : 'position';

        return $sortableField;
    }


    /**
     * Reset all positions and reorder the models.
     */
    public static function resetPositions()
    {
        $models = static::all();

        // Reset position starting from 'a'
        $position = 'a';

        $models->each(function ($model) use (&$position) {
            // Set the model's position
            $model->setAttribute(static::getSortableField(), $position);
            $model->save();

            // Increment the position for the next model (lexicographically)
            $position = static::incrementPosition($position);
        });
    }

    public static function incrementPosition($position)
    {
        $lastChar = substr($position, -1);
        if ($lastChar === 'z') {
            return $position . 'a';
        }

        return substr($position, 0, -1) . chr(ord($lastChar) + 1);
    }

    /**
     * Check if the current model is the first in the list.
     *
     * @return bool
     */
    public function isFirst()
    {
        return $this->getAttribute(static::getSortableField()) === static::getFirst()->getAttribute(static::getSortableField());
    }

    /**
     * Check if the current model is the last in the list.
     *
     * @return bool
     */
    public function isLast()
    {
        return $this->getAttribute(static::getSortableField()) === static::getLast()->getAttribute(static::getSortableField());
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    abstract public function newQuery();

    /**
     * Get the database connection for the model.
     *
     * @return \Illuminate\Database\Connection
     */
    abstract public function getConnection();

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    abstract public function getTable();

    /**
     * Save the model to the database.
     *
     * @param array $options
     *
     * @return bool
     */
    abstract public function save(array $options = []);

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     *
     * @return mixed
     */
    abstract public function getAttribute($key);

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    abstract public function setAttribute($key, $value);
}
