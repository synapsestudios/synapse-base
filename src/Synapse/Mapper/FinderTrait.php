<?php

namespace Synapse\Mapper;

use Synapse\Stdlib\Arr;

/**
 * Use this trait to add find functionality to AbstractMappers.
 */
trait FinderTrait
{
    /**
     * Find a single entity by specific field values
     *
     * @param  array  $fields Associative array where key is field and value is the value
     * @return AbstractEntity|bool
     */
    public function findBy(array $fields)
    {
        $query = $this->sql()->select();

        foreach ($fields as $name => $value) {
            $query->where([$name => $value]);
        }

        $data = $this->execute($query)->current();

        if (! $data || count($data) === 0) {
            return false;
        }

        return $data;
    }

    /**
     * Find a single entity by ID
     *
     * @param  int|string $id Entity ID
     * @return AbstractEntity|bool
     */
    public function findById($id)
    {
        return $this->findBy(['id' => $id]);
    }

    /**
     * Find all entities matching specific field values
     *
     * @param  array $fields  Associative array where key is field and value is the value
     * @param  array $options Array of options for this request
     * @return array          Array of AbstractEntity objects
     */
    public function findAllBy($fields, array $options = [])
    {
        $query = $this->sql()->select();

        foreach ($fields as $name => $value) {
            $query->where([$name => $value]);
        }

        $this->setOrder($query, $options);

        $entities = $this->execute($query)
            ->toEntityArray();

        return $entities;
    }

    /**
     * Find all entities in this table
     *
     * @param  array $options Array of options for this request
     * @return array          Array of AbstractEntity objects
     */
    public function findAll(array $options = [])
    {
        return $this->findAllBy([], $options);
    }

    /**
     * Set the order on the given query
     *
     * @param Select $query
     * @param array  $options Array of options which may or may not include `order`
     */
    protected function setOrder($query, $options)
    {
        if (! Arr::get($options, 'order')) {
            return $query;
        }

        // Can specify order as [['column', 'direction'], ['column', 'direction']].
        if (is_array($options['order'])) {
            foreach ($options['order'] as $order) {
                if (is_array($order)) {
                    $query->order(
                        Arr::get($order, 0).' '.Arr::get($order, 1)
                    );
                } else {
                    $query->order($key.' '.$order);
                }
            }
        } else { // Also support just a single ascending value
            return $query->order($options['order']);
        }

        return $query;
    }
}
