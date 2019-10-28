<?php

namespace Offspring\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Offspring\Permission\Contracts\ModelHasRole as ModelHasRoleContract;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ModelHasRole extends Model implements ModelHasRoleContract
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function __construct()
    {
        $this->setTable(config('permission.table_names.model_has_roles'));
    }

    public function saveModelHasRole($model_id, $model_type, $roles, $studio_id = null)
    {
        try {
            foreach ($roles as $k => $v) {
                $query = ModelHasRole::query()
                    ->where(config('permission.column_names.model_morph_key'), $model_id)
                    ->where('model_type', $model_type)
                    ->where('role_id', $v);

                if (isset($studio_id)) {
                    $query->where('studio_id', $studio_id);
                }
                $user_role = $query->first();

                if (!isset($user_role)) {
                    $user_role = new ModelHasRole();
                    $user_role->fill([
                        config('permission.column_names.model_morph_key') => $model_id,
                        'model_type' => $model_type,
                        'role_id' => $v,
                        'studio_id' => $studio_id ?? 0
                    ]);

                    $user_role->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function removeModelHasRole($model_id, $model_type, $roles, $studio_id = null)
    {
        try {
            foreach ($roles as $k => $v) {
                $query = ModelHasRole::query()
                    ->where(config('permission.column_names.model_morph_key'), $model_id)
                    ->where('model_type', $model_type)
                    ->where('role_id', $v);

                if (isset($studio_id)) {
                    $query->where('studio_id', $studio_id);
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
}