<?php

namespace Syn\LaravelSwaggerJsonApiGenerator\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $type
 * @property string $name
 * @property array $component
 */
class SwaggerComponent extends Model
{
    protected $table = 'swagger_components';

    public const FIELD_TYPE = 'type';
    public const FIELD_NAME = 'name';
    public const FIELD_COMPONENT = 'component';

    protected $fillable = [
        self::FIELD_TYPE,
        self::FIELD_NAME,
        self::FIELD_COMPONENT,
    ];

    protected $casts = [
        self::FIELD_COMPONENT => 'json',
    ];
    
    public static function getForName(string $name): null|self|Model
    {
        return self::query()->where(self::FIELD_NAME, 'like', $name)->first();
    }
}
