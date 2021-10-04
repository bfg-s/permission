<?php

namespace Bfg\Permission\Models;

use Bfg\Permission\PermissionFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Role.
 * @package Bfg\Permission\Models
 */
class Role extends Model
{
    /**
     * @var string
     */
    protected $table = 'roles';

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'priority' => 'integer',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Role constructor.
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (! isset($this->attributes['priority']) || ! $this->attributes['priority']) {
            $this->attributes['priority'] = static::count();
        }
    }

    /**
     * Mutator if slug not exists.
     * @param $value
     */
    public function setSlugAttribute($value)
    {
        if (! $value) {
            $this->attributes['slug'] = strtolower(\Str::slug($this->name));
        } else {
            $this->attributes['slug'] = $value;
        }
    }

    /**
     * All role users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            app(\Bfg\Permission\UserRepository::class)->model_class,
            'user_roles',
            'role_id',
            'user_id'
        );
    }

    /**
     * @param  string  $rule
     * @return bool
     */
    public function allow(string $rule): bool
    {
        return app(PermissionFactory::class)
            ->set(['rule-'.$this->slug, $rule], true)
            ->save();
    }

    /**
     * @param  string  $rule
     * @return bool
     */
    public function disallow(string $rule): bool
    {
        return app(PermissionFactory::class)
            ->set(['rule-'.$this->slug, $rule], false)
            ->save();
    }
}
