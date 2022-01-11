<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Guest extends Model
{
    use HasFactory;

    /**
     * Generate a random name probably unique
     * @param int $word_count the number of words of the name
     * @return string the generated name
     */
    public static function generateName($word_count = 3)
    {
        $file = Storage::disk('local')->get(config('sparty.wordlist_location'));
        $words = preg_split('/\s+/', $file);

        $max = count($words) - 1;
        $name = "";

        for ($i = 0; $i < $word_count; ++$i)
        {
            $name .= $words[rand(0, $max)] . '-';
        }

        return substr($name, 0, -1);
    }
}
