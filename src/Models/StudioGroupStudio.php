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

    public function getStudioInGroup($studio_ids,$group_ids)
    {
        try {

            $query = StudioGroupStudio::query()
                ->whereIn('studio_group_id', $group_ids);
            if (is_array($studio_ids)) {
                $query->whereIn('studio_id', $studio_ids);
            } else {
                $query->where('studio_id', $studio_ids);
            }

            return $query->get();

        } catch (\Exception $e) {
            return null;
        }
    }
}