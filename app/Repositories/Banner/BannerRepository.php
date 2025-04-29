<?php

namespace App\Repositories\Banner;

use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerRepository
{
    public function getAll()
    {
        return Banner::query();
    }

    public function find($id)
    {
        return Banner::find($id);
    }

    public function create(array $data)
    {
        return Banner::create($data);
    }

    public function update($id, array $data)
    {
        $banner = $this->find($id);

        if ($banner) {
            $banner->update($data);
        }
        return $banner;
    }

    public function delete($id)
    {
        $banner = $this->find($id);

        if ($banner) {
            if ($banner->image) {
                $path = str_replace(asset('storage') . '/', '', $banner->image);

                Storage::delete('public/' . $path);
            }

            $banner->delete();
        }

        return $banner;
    }
}
