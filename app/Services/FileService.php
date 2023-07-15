<?php

namespace App\Services;

use App\Models\File;

class FileService
{
    public static function deleteExpiredFile(): bool
    {
        return File::query()
            ->where('expired_at', '<', now())
            ->delete();
    }

    public static function save(array $data): array
    {
        $file = new File();
        $file->name = $data['name'];
        $file->path = $data['path'];
        $file->mime_type = $data['mime_type'];
        $file->size = $data['size'];
        $file->hash = $data['hash'];
        $file->ext = $data['ext'];
        $file->driver = $data['driver'];
        $file->save();
        return $file->toArray();
    }

    public function get(int $id): array
    {
        $file = File::query()->find($id);
        if ($file) {
            return $file->toArray();
        }
        return [];
    }
}
