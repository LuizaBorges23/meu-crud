<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Spatie\MediaLibrary\InteractsWithMedia;
use \Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['name', 'description', 'price', 'image'])]
class Product extends Model implements \Spatie\MediaLibrary\HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit(\Spatie\Image\Enums\Fit::Crop, 160, 160);
    }
}
