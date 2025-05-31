<?php

namespace System\Core\Data;

use Router\Request\Model\Model;

class DataModel extends \Database\Orm\Model implements Model
{
    public function setValues(array $values)
    {
        foreach ($values as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function getValues(): array
    {
        $data = get_object_vars($this);

        unset($data['attr']);
        unset($data['fields']);

        return $data;
    }
}
