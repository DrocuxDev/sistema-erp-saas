<?php

namespace App\Models\Concerns;

trait PersistsAuditably
{
    public function save(array $options = [])
    {
        $connection = $this->getConnection();
        $persistir = fn () => parent::save($options);

        return $connection->transaction($persistir);
    }

    public function delete()
    {
        $connection = $this->getConnection();
        $eliminar = fn () => parent::delete();

        return $connection->transaction($eliminar);
    }
}
