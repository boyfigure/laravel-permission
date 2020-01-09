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

    public function checkGroupHasAllStudio($group_ids)
    {
        try {
            $query = StudioGroup::query()
                ->whereIn('is_all_studio', 1);
            if (is_array($group_ids)) {
                $query->whereIn('id', $group_ids);
            } else {
                $query->where('id', $group_ids);
            }

            return $query->first();

        } catch (\Exception $e) {
            return null;
        }
    }

    public function groupStudios()
    {
        return $this->hasMany(config('permission.models.studio_group_studios'), 'studio_group_id');
    }
}