<?php

return [

    'models' => [

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your permissions. Of course, it
         * is often just the "Permission" model but you may use whatever you like.
         *
         * The model you want to use as a Permission model needs to implement the
         * `Offspring\Permission\Contracts\Permission` contract.
         */

        'permission' => Offspring\Permission\Models\Permission::class,

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * Eloquent model should be used to retrieve your roles. Of course, it
         * is often just the "Role" model but you may use whatever you like.
         *
         * The model you want to use as a Role model needs to implement the
         * `Offspring\Permission\Contracts\Role` contract.
         */

        'role' => Offspring\Permission\Models\Role::class,

        'model_has_permissions' => Offspring\Permission\Models\ModelHasPermissions::class,

        'model_has_roles' => Offspring\Permission\Models\ModelHasRole::class,
        'studio_groups' => Offspring\Permission\Models\StudioGroup::class,
        'studio_group_studios' => Offspring\Permission\Models\StudioGroupStudio::class,

    ],

    'table_names' => [

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'roles' => 'roles',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your permissions. We have chosen a basic
         * default value but you may easily change it to any table you like.
         */

        'permissions' => 'permissions',

        /*
         * When using the "HasPermissions" trait from this package, we need to know which
         * table should be used to retrieve your models permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_permissions' => 'model_has_permissions',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your models roles. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'model_has_roles' => 'model_has_roles',

        /*
         * When using the "HasRoles" trait from this package, we need to know which
         * table should be used to retrieve your roles permissions. We have chosen a
         * basic default value but you may easily change it to any table you like.
         */

        'role_has_permissions' => 'role_has_permissions',


        'studio_groups' => 'studio_groups',

        'studio_group_studios' => 'studio_group_studios'
    ],

    'column_names' => [

        /*
         * Change this if you want to name the related model primary key other than
         * `model_id`.
         *
         * For example, this would be nice if your primary keys are all UUIDs. In
         * that case, name this `model_uuid`.
         */

        'model_morph_key' => 'model_id',
        'model_second_morph_key' => 'model_second_id',
    ],

    /*
     * When set to true, the required permission/role names are added to the exception
     * message. This could be considered an information leak in some contexts, so
     * the default setting is false here for optimum safety.
     */

    'display_permission_in_exception' => false,
    'role_super_admin' => 'SUPER_ADMIN',


    'cache' => [

        /*
         * By default all permissions are cached for 24 hours to speed up performance.
         * When permissions or roles are updated the cache is flushed automatically.
         */

        'expiration_time' => 1440,

        /*
         * The cache key used to store all permissions.
         */

        'key' => 'offspring.permission.cache',
        'group_by_studio_key' => 'offspring.role.cache.group_studio',
        'user_studio_in_group_key' => 'offspring.role.cache.user_studio_in_group_key',
        'user_role_studio_group_key' => 'offspring.role.cache.user_role_studio_group_key',
        'user_role_studio_key' => 'offspring.role.cache.user_role_studio_key',
        'user_studio_key' => 'offspring.role.cache.user_studio_key',
        'user_group_role_key' => 'offspring.role.cache.user_group_role_key',
        'user_has_all_studio'  => 'offspring.role.cache.user_has_all_studio',
        'super_admin' => 'offspring.role.cache.super_admin',
        'all_cache_tags' => 'offspring.role.cache.all_cache_tags',
        'all_cache_by_user_tags' => 'offspring.role.cache.all_cache_by_user_tags',
        /*
         * When checking for a permission against a model by passing a Permission
         * instance to the check, this key determines what attribute on the
         * Permissions model is used to cache against.
         *
         * Ideally, this should match your preferred way of checking permissions, eg:
         * `$user->can('view-posts')` would be 'name'.
         */

        'model_key' => 'name',

        /*
         * You may optionally indicate a specific cache driver to use for permission and
         * role caching using any of the `store` drivers listed in the cache.php config
         * file. Using 'default' here means to use the `default` set in cache.php.
         */

        'store' => 'default',
    ],
];
