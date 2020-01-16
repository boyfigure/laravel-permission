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

    public function userHasAllStudio()
    {
        try {
            $user_id = $this->id;
            $query = StudioGroup::query()
                ->where('is_all_studio', 1)
                ->whereHas('groupStudios', function ($q) use ($user_id) {
                    $q->whereIn('user_id', $user_id);
                });
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