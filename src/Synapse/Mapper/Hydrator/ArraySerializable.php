<?php
namespace Synapse\Mapper\Hydrator;

class ArraySerializable extends \Zend\Stdlib\Hydrator\ArraySerializable
{
    public function hydrate(array $data, $object)
    {
        $object = parent::hydrate($data, $object);
        if (is_callable([$object, 'setAsClean'])) {
            $object->setAsClean();
        }

        return $object;
    }
}
