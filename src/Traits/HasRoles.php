<?php

namespace Offspring\Permission\Traits;

use Illuminate\Support\Collection;
use Offspring\Permission\Contracts\Role;
use Illuminate\Database\Eloquent\Builder;
use Offspring\Permission\Models\StudioGroupStudio;
use Offspring\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasRoles
{
    use HasPermissions;

    private $roleClass;
    private $modelHasRoleClass;
    private $studioGroupStudioClass;
    private $studioGroupClass;

    private $not_group_studio = 0;
    private $group_studio = 1;


    public static function bootHasRoles()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                return;
            }

            $model->roles()->detach();
        });
    }

    public function getRoleClass()
    {
        if (!isset($this->roleClass)) {
            $this->roleClass = app(PermissionRegistrar::class)->getRoleClass();
        }

        return $this->roleClass;
    }

    public function getModelHasRoleClass()
    {
        if (!isset($this->modelHasRoleClass)) {
            $this->modelHasRoleClass = app(PermissionRegistrar::class)->getModelHasRoleClass();
        }

        return $this->modelHasRoleClass;
    }

    public function getStudioGroupStudioClass()
    {
        if (!isset($this->studioGroupStudioClass)) {
            $this->studioGroupStudioClass = app(PermissionRegistrar::class)->getModelStudioGroupStudioClass();
        }

        return $this->studioGroupStudioClass;
    }

    public function getStudioGroupClass()
    {
        if (!isset($this->studioGroupClass)) {
            $this->studioGroupClass = app(PermissionRegistrar::class)->getStudioGroupClass();
        }

        return $this->studioGroupClass;
    }

    protected function getCache()
    {
        if (!isset($this->cache_role)) {
            $this->cache_role = app(PermissionRegistrar::class)->getCacheStore();
        }

        return $this->cache_role;
    }

    /**
     * A model may have multiple roles.
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        )->withPivot('studio_id', 'group_type');
    }

    /**
     * Scope the model query to certain roles only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|\Offspring\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     * @param string $guard
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole(Builder $query, $roles, $guard = null): Builder
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $roles = array_map(function ($role) use ($guard) {
            if ($role instanceof Role) {
                return $role;
            }

            $method = is_numeric($role) ? 'findById' : 'findByName';
            $guard = $guard ?: $this->getDefaultGuardName();

            return $this->getRoleClass()->{$method}($role, $guard);
        }, $roles);

        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->where(function ($query) use ($roles) {
                foreach ($roles as $role) {
                    $query->orWhere(config('permission.table_names.roles') . '.id', $role->id);
                }
            });
        });
    }

    /**
     * Assign the given role to the model.
     *
     * @param int|null $studio_id
     * @param int|null $group_type
     * @param array|string|\Offspring\Permission\Contracts\Role ...$roles
     *
     * @return $this
     */
    public function assignRole($studio_id, $group_type = 0, ...$roles)
    {
        $modelHasRoleClass = $this->getModelHasRoleClass();

        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (empty($role)) {
                    return false;
                }

                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->each(function ($role) {
                $this->ensureModelSharesGuard($role);
            })
            ->map->id
            ->all();

        $model = $this->getModel();
        $model_type = $this->roles()->getMorphClass();
        if ($model->exists) {
            $user_role = $modelHasRoleClass->saveModelHasRole($this->id, $model_type, $roles, $studio_id, $group_type);
            $model->load('roles');
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($roles, $model, $modelHasRoleClass, $model_type, $studio_id, $group_type) {
                    static $modelLastFiredOn;
                    if ($modelLastFiredOn !== null && $modelLastFiredOn === $model) {
                        return;
                    }
                    $user_role = $modelHasRoleClass->saveModelHasRole($object->id, $model_type, $roles, $studio_id, $group_type);
                    $object->load('roles');
                    $modelLastFiredOn = $object;
                });
        }

        $this->forgetCachedPermissions();
        $this->flushCachedRole(config('permission.cache.all_cache_by_user_tags') . '.' . $this->id);

        return $this;
    }

    /**
     * Revoke the given role from the model.
     *
     * @param int|null $studio_id
     * @param int|null $group_type
     * @param array|\Offspring\Permission\Contracts\Role ...$roles
     *
     *
     * @return $this
     */

    public function removeRole($studio_id, $group_type = 0, ...$roles)
    {
        $modelHasRoleClass = $this->getModelHasRoleClass();

        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (empty($role)) {
                    return false;
                }

                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->each(function ($role) {
                $this->ensureModelSharesGuard($role);
            })
            ->map->id
            ->all();

        $model_type = $this->roles()->getMorphClass();
        $user_role = $modelHasRoleClass->removeModelHasRole($this->id, $model_type, $roles, $studio_id, $group_type);
        $this->load('roles');

        $this->forgetCachedPermissions();
        $this->flushCachedRole(config('permission.cache.all_cache_by_user_tags') . '.' . $this->id);

        return $this;
    }

    /**
     * Remove all current roles and set the given ones.
     * @param int|null $studio_id
     * @param int|null $group_type
     * @param array|\Offspring\Permission\Contracts\Role|string ...$roles
     *
     * @return $this
     */
    public function syncRoles($studio_id, $group_type = 0, ...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (empty($role)) {
                    return false;
                }

                return $this->getStoredRole($role);
            })
            ->filter(function ($role) {
                return $role instanceof Role;
            })
            ->each(function ($role) {
                $this->ensureModelSharesGuard($role);
            })
            ->map->id
            ->all();

        $current_roles = $this->roles->pluck('id')->toArray();
        $role_remove = array_unique(array_diff($current_roles, $roles));
        $role_update = array_unique(array_diff($roles, $current_roles));
        if (!empty($role_remove)) {
            foreach ($role_remove as $k => $v) {
                $this->removeRole($studio_id, $group_type, $v);
            }
        }
        if (!empty($role_update)) {
            $this->assignRole($studio_id, $group_type, $role_update);
        }
        return $this;
    }


    public function hasRole($roles, string $guard = null, $studio_id = null): bool
    {

        if ($this->isSuperAdmin()) {
            return true;
        }

        $cache = $this->getCache();
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                //need optimal call database
                if ($this->hasRole($role, $guard, $studio_id)) {
                    return true;
                }
            }

            return false;
        }
        if (isset($studio_id)) {
            //check group
            $modelHasRoleClass = $this->getModelHasRoleClass();
            $group = $modelHasRoleClass->getGroupByStudio($studio_id);
            //get user role
//            $cache_tag = config('permission.cache.user_role_key');
//            $cache_key = $cache_tag . '.' . $this->id . '.' . $studio_id;

            $data = $this->roles()->where(function (Builder $query) use ($studio_id, $group) {
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
                return $query;
            })->get();

//            $data = $cache->tags($cache_tag)->remember($cache_key, config('permission.cache.expiration_time'), function () use ($studio_id, $group) {
//                return $this->roles()->where(function (Builder $query) use ($studio_id, $group) {
//                    if (!$group->isEmpty()) {
//                        $group = $group->pluck('id')->toArray();
//                        $query->where(function ($q) use ($group) {
//                            $q->where('group_type', 1)
//                                ->whereIn('studio_id', $group);
//                        });
//                        $query->orWhere(function ($q) use ($studio_id) {
//                            $q->where('group_type', 0)
//                                ->where('studio_id', $studio_id);
//                        });
//                    } else {
//                        $query->where('group_type', 0)
//                            ->where('studio_id', $studio_id);
//                    }
//                    return $query;
//                })->get();
//            });
        } else {
            $data = $this->roles;
        }

        if (is_string($roles)) {
            return $guard
                ? $data->where('guard_name', $guard)->contains('name', $roles)
                : $data->contains('name', $roles);
        }

        if (is_int($roles)) {
            return $guard
                ? $data->where('guard_name', $guard)->contains('id', $roles)
                : $data->contains('id', $roles);
        }

        if ($roles instanceof Role) {
            return $data->contains('id', $roles->id);
        }

        return $roles->intersect($guard ? $data->where('guard_name', $guard) : $data)->isNotEmpty();
    }

    /**
     * Determine if the model has any of the given role(s).
     *
     * @param string|array|\Offspring\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     * @param int|null $studio_id
     *
     * @return bool
     */
    public function hasAnyRole($roles, $studio_id = null): bool
    {
        return $this->hasRole($roles, null, $studio_id);
    }

    public function hasAnyRolesAllStudio($roles, $studios): bool
    {
        $check = true;
        if ($this->isSuperAdmin()) {
            return true;
        }
        foreach ($studios as $k => $studio_id) {
            if (!$this->hasRole($roles, null, $studio_id)) {
                $check = false;
            }
        }
        return $check;
    }

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param string|\Offspring\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     * @param string|null $guard
     * @param int|null $studio_id
     * @return bool
     */
    public function hasAllRoles($roles, string $guard = null, int $studio_id = null): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }
        if (isset($studio_id)) {
            $data = $this->roles()->where(function (Builder $query) use ($studio_id) {
                return $query
                    ->where('studio_id', $studio_id);
            })->get();

        } else {
            $data = $this->roles;
        }


        if (is_string($roles)) {
            return $guard
                ? $data->where('guard_name', $guard)->contains('name', $roles)
                : $data->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $data->contains('id', $roles->id);
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect(
                $guard
                    ? $data->where('guard_name', $guard)->pluck('name')
                    : $this->getRoleNames()) == $roles;
    }

    /**
     * Return all permissions directly coupled to the model.
     */
    public function getDirectPermissions(): Collection
    {
        return $this->permissions;
    }

    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    public function getRoleStudioGroup()
    {
        $cache = $this->getCache();
        $cache_tag = [
            config('permission.cache.all_cache_tags'),
            config('permission.cache.all_cache_by_user_tags') . '.' . $this->id,
            config('permission.cache.user_role_studio_group_key'),
        ];
        $cache_key = config('permission.cache.user_role_studio_group_key') . '.' . $this->id;
        $result = $cache->tags($cache_tag)->get($cache_key);
        if (isset($result)) {
            return $result;
        }

        $data = $this->roles()->get();
        $role = [
            'is_empty' => true,
            'studios' => [],
            'groups' => []
        ];

        if (!$data->isEmpty()) {
            $role['is_empty'] = false;
            foreach ($data as $k => $v) {
                if ($v->pivot->group_type == $this->group_studio) {
                    $role['groups'][] = [
                        'role_name' => $v->name,
                        'studio_group_id' => $v->pivot->studio_id
                    ];
                } else {
                    $role['studios'][] = [
                        'role_name' => $v->name,
                        'studio_id' => $v->pivot->studio_id
                    ];
                }
            }
        }

        $cache->forget($cache_key);
        if (isset($data)) {
            $cache->tags($cache_tag)->put($cache_key, $role, config('permission.cache.expiration_time'));
            return $role;
        }
        $cache->tags($cache_tag)->put($cache_key, $role, config('permission.cache.expiration_time'));

        return $role;
    }

    public function getUserStudio()
    {

    }

    public function flushCachedRole($cache_tag)
    {
        $cache = $this->getCache();

        return $cache->tags($cache_tag)->flush();
    }


    protected function getStoredRole($role): Role
    {
        $roleClass = $this->getRoleClass();

        if (is_numeric($role)) {
            return $roleClass->findById($role, $this->getDefaultGuardName());
        }

        if (is_string($role)) {
            return $roleClass->findByName($role, $this->getDefaultGuardName());
        }

        return $role;
    }

    protected function convertPipeToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (!in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }

    public function getFirstUserByRole($role, $studio_id)
    {
        $modelHasRoleClass = $this->getModelHasRoleClass();
        $model_type = $this->roles()->getMorphClass();
        $role = $this->getStoredRole($role);
        if (!$role) {
            return false;
        }
        return $modelHasRoleClass->getFirstModelHasRoleByRole($model_type, $role->id, $studio_id);
    }

    public function isSuperAdmin()
    {
        $cache = $this->getCache();
        $cache_tag = [
            config('permission.cache.all_cache_tags'),
            config('permission.cache.all_cache_by_user_tags') . '.' . $this->id,
            md5(config('permission.cache.super_admin')),
        ];
        $cache_key = md5(config('permission.cache.super_admin')) . '.' . $this->id;
        $result = $cache->tags($cache_tag)->get($cache_key);
        if (isset($result)) {
            return $result;
        }

        $data = $this->roles()->where(function (Builder $query) {
            return $query
                ->where('studio_id', 0)
                ->where('name', config('permission.role_super_admin'));
        })->first();

        $cache->forget($cache_key);
        if (isset($data)) {
            $cache->tags($cache_tag)->put($cache_key, true, config('permission.cache.expiration_time'));
            return true;
        }
        $cache->tags($cache_tag)->put($cache_key, false, config('permission.cache.expiration_time'));
        return false;
    }

    public function getStudioInGroup($group_ids)
    {
        $studioGroupStudioClass = $this->getStudioGroupStudioClass();

        try {
            $cache = $this->getCache();
            $cache_tag = [
                config('permission.cache.all_cache_tags'),
                config('permission.cache.all_cache_by_user_tags') . '.' . $this->id,
                config('permission.cache.user_studio_in_group_key'),
            ];
            if (is_array($group_ids)) {
                $cache_key = config('permission.cache.user_studio_in_group_key') . '.' . implode('.', $group_ids);
            } else {
                $cache_key = config('permission.cache.user_studio_in_group_key') . '.' . $group_ids;
            }
            $data = $cache->tags($cache_tag)->remember($cache_key, config('permission.cache.expiration_time'), function () use ($studioGroupStudioClass, $group_ids) {
                $query = $studioGroupStudioClass::query();
                if (is_array($group_ids)) {
                    $query->whereIn('studio_group_id', $group_ids);
                } else {
                    $query->where('studio_group_id', $group_ids);
                }
                return $query->get()->toArray();
            });

            if (count($data) > 0) {
                return $data;
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    public function checkGroupHasAllStudio($group_ids)
    {
        $studioGroupStudioClass = $this->getStudioGroupClass();

        return $studioGroupStudioClass->checkGroupHasAllStudio($group_ids);
    }
}
