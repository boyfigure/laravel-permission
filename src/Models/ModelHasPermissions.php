<?php

namespace Offspring\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Offspring\Permission\Contracts\ModelHasPermissions as ModelHasPermissionsContract;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ModelHasPermissions extends Model implements ModelHasPermissionsContract
{
    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? config('auth.defaults.guard');

        parent::__construct($attributes);

        $this->setTable(config('permission.table_names.model_has_permissions'));
    }
}