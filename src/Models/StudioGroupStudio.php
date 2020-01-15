<?php

namespace Offspring\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Offspring\Permission\Contracts\StudioGroupStudio as StudioGroupStudioContract;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudioGroupStudio extends Model implements StudioGroupStudioContract
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function __construct()
    {
        $this->setTable(config('permission.table_names.studio_group_studios'));
    }

    public static function getTableName()
    {
        $instance = new static;

        return $instance->getTable();
    }

}