<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Setting extends Model
{
    use HasFactory;


    /**
     * @var string[]
     */
    protected $fillable = ['key', 'value'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public static function getValue(string $key, $default = null): mixed
    {
        $record = static::where('key', $key)->first();
        return $record ? $record->value : $default;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function setValue($key, $value): mixed
    {
        return static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
