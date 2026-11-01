<?php

namespace App\Services;

use App\Models\DailyWorkPhoto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class DailyWorkPhotoService
{
    public function storePhotosForDailyWork(int $dailyWorkId, array $photos): void
    {
        foreach ($photos as $photo) {
            $originalName = $photo->getClientOriginalName();
            $extension = $photo->getClientOriginalExtension();
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $safeName = $baseName.'-'.uniqid().'.'.$extension;
            $path = $photo->storeAs('daily-works/'.$dailyWorkId, $safeName, 'public');

            DailyWorkPhoto::create([
                'daily_work_id' => $dailyWorkId,
                'photo_path' => $path,
                'original_file_name' => $originalName,
            ]);
        }
    }

    /**
     * @return Collection<int, DailyWorkPhoto>
     */
    public function deletePhotosForDailyWork(int $dailyWorkId): Collection
    {
        $photos = DailyWorkPhoto::query()->where('daily_work_id', $dailyWorkId)->get();

        foreach ($photos as $photo) {
            if ($photo->photo_path && Storage::disk('public')->exists($photo->photo_path)) {
                Storage::disk('public')->delete($photo->photo_path);
            }

            $photo->delete();
        }

        return $photos;
    }

    public function deletePhoto(int $photoId): bool
    {
        $photo = DailyWorkPhoto::findOrFail($photoId);

        if ($photo->photo_path && Storage::disk('public')->exists($photo->photo_path)) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        return (bool) $photo->delete();
    }
}
