<?php

namespace Offspring\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Offspring\Permission\Contracts\ModelHasRole as ModelHasRoleContract;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ModelHasRole extends Model implements ModelHasRoleContract
{
    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? config('auth.defaults.guard');

        parent::__construct($attributes);

        $this->setTable(config('permission.table_names.model_has_roles'));
    }

    public function saveModelHasRole($model_id, $model_type, $role, $studio_id = null)
    {
        try {
            foreach ($role as $k => $v) {
                $user_role = ModelHasRole::query()
                    ->where(config('permission.column_names.model_morph_key'), $model_id)
                    ->where('model_type', $model_type)
                    ->where('role_id', $v)
                    ->where('studio_id', $studio_id)
                    ->first();
                if (!isset($user_role)) {
                    $user_role = new ModelHasRole();
                    $user_role->fill([
                        config('permission.column_names.model_morph_key') => $model_id,
                        'model_type' => $model_type,
                        'role_id' => $v,
                        'studio_id' => $studio_id
                    ]);
                    $user_role->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}