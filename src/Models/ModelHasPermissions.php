<?php

namespace Offspring\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Offspring\Permission\Contracts\ModelHasPermissions as ModelHasPermissionsContract;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ModelHasPermissions extends Model implements ModelHasPermissionsContract
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function __construct()
    {

        $this->setTable(config('permission.table_names.model_has_permissions'));
    }
}