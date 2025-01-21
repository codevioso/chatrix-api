<?php

namespace App\Services;

use App\Enums\MediaType;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    private $colors = ['0652DD','009432','1B1464','6F1E51','3c40c6'];

    /**
     * Generate avatar from name
     *
     * @param string $name
     * @return string
     */
    public static function generate_avatar(string $name): string
    {
        $name = Str::slug($name);
        $color = self::$colors[rand(0, count(self::$colors) - 1)];
        $file = file_get_contents("https://ui-avatars.com/api/?name=$name&size=100&background=".$color."&color=fff");
        $filename = 'media/avatar/'.$name.'.png';
        Storage::disk('public')->put($filename, $file);
        return $filename;
    }

    public static function get_file_name(MediaType $mediaType):string
    {
        if($mediaType == MediaType::AVATAR){
            $name = Str::slug(User::session_user()->name);
            return 'media/avatar/'.$name;
        } elseif ($mediaType == MediaType::IMAGE) {
            return 'media/image/'.Str::random(10);
        } elseif ($mediaType == MediaType::VIDEO) {
            return 'media/video/'.Str::random(10);
        } elseif ($mediaType == MediaType::AUDIO) {
            return 'media/audio/'.Str::random(10);
        } elseif ($mediaType == MediaType::DOCUMENT) {
            return 'media/document/'.Str::random(10);
        }
    }
}
