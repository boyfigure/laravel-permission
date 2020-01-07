<?php

namespace Offspring\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Offspring\Permission\Contracts\StudioGroup as StudioGroupContract;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StudioGroup extends Model implements StudioGroupContract
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function __construct()
    {

        $this->setTable(config('permission.table_names.studio_groups'));
    }

    public static function getTableName()
    {
        $instance = new static;

        return $instance->getTable();
    }

}