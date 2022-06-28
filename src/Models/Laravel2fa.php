<?php

namespace Stickee\Laravel2fa\Models;

use Illuminate\Database\Eloquent\Model;

class Laravel2fa extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_type',
        'user_id',
        'enabled',
        'data',
    ];

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return config('laravel-2fa.table_name');
    }

    /**
     * Get the owning user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user()
    {
        return $this->morphTo();
    }

    /**
     * Get the data.
     *
     * @param array $value
     * @return array
     */
    public function getDataAttribute(): array
    {
        return empty($this->attributes['data'])
            ? []
            : json_decode(decrypt($this->attributes['data']), true);
    }

    /**
     * Set the data.
     *
     * @param array $value
     * @return void
     */
    public function setDataAttribute(array $value)
    {
        $this->attributes['data'] = encrypt(json_encode($value));
    }

    /**
     * Create an instance of a Laravel2fa model assigned to the given model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return null|\Stickee\Laravel2fa\Models\Laravel2fa
     */
     public static function createByModel($model)
     {
        return self::create([
            'user_type' => $model->getMorphClass(),
            'user_id' => $model->getKey(),
            'enabled' => config('laravel-2fa.required'),
            'data' => [],
        ]);
     }

    /**
     * Get the first instance of a Laravel2fa model with the given user type and user id.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return null|\Stickee\Laravel2fa\Models\Laravel2fa
     */
    public static function getByModel($model)
    {
        return self::where('user_type', $model->getMorphClass())
            ->where('user_id', $model->getKey())
            ->first();
    }
}
