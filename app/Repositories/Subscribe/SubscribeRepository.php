<?php

namespace App\Repositories\Subscribe;

use App\Models\Subscribe;

class SubscribeRepository
{
    public function getAll($request)
    {
        return Subscribe::query();
    }

    public function getAllSubscribers()
    {
        return Subscribe::all();
    }

    public function find($id)
    {
        return Subscribe::find($id);
    }

    public function create(array $data)
    {
        return Subscribe::create($data);
    }

    public function update($id, array $data)
    {
        $subscribe = $this->find($id);

        if ($subscribe) {
            $subscribe->update($data);
        }
        return $subscribe;
    }

    public function delete($id)
    {
        $subscribe = $this->find($id);
        if ($subscribe) {
            $subscribe->delete();
        }
        return $subscribe;
    }
}
