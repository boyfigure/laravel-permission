<?php

namespace Offspring\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Offspring\Permission\Contracts\ModelHasRole as ModelHasRoleContract;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Offspring\Permission\PermissionRegistrar;

class ModelHasRole extends Model implements ModelHasRoleContract
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function __construct()
    {
        $this->setTable(config('permission.table_names.model_has_roles'));
    }

    public function saveModelHasRole($model_id, $model_type, $roles, $studio_id = null, $group_type = 0)
    {
        $role_super_admin = Role::query()->where('name', config('permission.role_super_admin'))->first();
        try {
            foreach ($roles as $k => $v) {
                if (isset($role_super_admin) and $role_super_admin->getKey() == $v) {
                    return false;
                }
                $query = ModelHasRole::query()
                    ->where(config('permission.column_names.model_morph_key'), $model_id)
                    ->where('model_type', $model_type)
                    ->where('role_id', $v);

                if (isset($studio_id)) {
                    $query->where('studio_id', $studio_id);
                    $query->where('group_type', $group_type);
                }
                $user_role = $query->first();

                if (!isset($user_role)) {
                    $user_role = new ModelHasRole();
                    $user_role->fill([
                        config('permission.column_names.model_morph_key') => $model_id,
                        'model_type' => $model_type,
                        'role_id' => $v,
                        'studio_id' => $studio_id ?? 0,
                        'group_type' => $group_type ?? 0
                    ]);

                    $user_role->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function removeModelHasRole($model_id, $model_type, $roles, $studio_id = null, $group_type = 0)
    {
        try {
            foreach ($roles as $k => $v) {
                $query = ModelHasRole::query()
                    ->where(config('permission.column_names.model_morph_key'), $model_id)
                    ->where('model_type', $model_type)
                    ->where('role_id', $v);

                if (isset($studio_id)) {
                    $query->where('studio_id', $studio_id);
                    $query->where('group_type', $group_type);
                }
                $query->delete();
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getTableName()
    {
        $instance = new static;

        return $instance->getTable();
    }

    public function getFirstModelHasRoleByRole($model_type, $role, $studio_id = null)
    {
        try {

            $query = ModelHasRole::query()
                ->where('model_type', $model_type)
                ->where('role_id', $role);

            if (isset($studio_id)) {
                $group = $this->getGroupByStudio($studio_id);

                $query->where(function (Builder $query) use ($studio_id, $group) {
                    if (!$group->isEmpty()) {
                        $group = $group->pluck('id')->toArray();
                        $query->where(function ($q) use ($group) {
                            $q->where('group_type', 1)
                                ->whereIn('studio_id', $group);
                        });
                        $query->orWhere(function ($q) use ($studio_id) {
                            $q->where('group_type', 0)
                                ->where('studio_id', $studio_id);
                        });
                    } else {
                        $query->where('group_type', 0)
                            ->where('studio_id', $studio_id);
                    }
                });
                $user = $query->first();
                if (isset($user)) {
                    $user = $user->toArray();
                    return $user[config('permission.column_names.model_morph_key')];
                }
            }
            return false;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function getGroupByStudio($studio_id)
    {
        //check group
        $cache_tag = config('permission.cache.group_studio_key');
        $cache_key = $cache_tag . '.' . $studio_id;

        $group = $this->getCache()->tags($cache_tag)->remember($cache_key, config('permission.cache.expiration_time'), function () use ($studio_id) {
            return StudioGroup::query()->whereHas('groupStudios', function ($q) use ($studio_id) {
                $q->where('studio_id', $studio_id);
            })->orWhere('is_all_studio', 1)
                ->get();
        });
        return $group;
    }

    protected function getCache()
    {
        if (!isset($this->cache_role)) {
            $this->cache_role = app(PermissionRegistrar::class)->getCacheStore();
        }

        return $this->cache_role;
    }
}