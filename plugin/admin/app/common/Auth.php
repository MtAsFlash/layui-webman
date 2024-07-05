<?php
// +----------------------------------------------------------------------
// | huicmf [ huicmf快速开发框架 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2022~2024 https://xiaohuihui.cc All rights reserved.
// +----------------------------------------------------------------------
// | Author: 小灰灰 <762229008@qq.com>
// +----------------------------------------------------------------------
// | Info:
// +----------------------------------------------------------------------

namespace plugin\admin\app\common;

use plugin\admin\app\model\Admin;
use plugin\admin\app\model\AdminRole;
use plugin\admin\app\model\Role;
use plugin\admin\app\model\Rule;
use think\facade\Db;

class Auth
{

    /**
     * 获取权限范围内的所有角色id
     *
     * @param bool $with_self
     *
     * @return array
     */
    public static function getScopeRoleIds(bool $with_self = false): array
    {
        if ( ! $admin = admin()) {
            return [];
        }
        $role_ids = $admin['roles'];
        $rules    = Role::whereIn('id', $role_ids)->column('rules');
        if ($rules && in_array('*', $rules)) {
            return Role::column('id');
        }

        $roles       = Role::select()->toArray();
        $tree        = new Tree($roles);
        $descendants = $tree->getDescendant($role_ids, $with_self);

        return array_column($descendants, 'id');
    }

    /**
     * 获取权限范围内的所有管理员id
     *
     * @param bool $with_self
     *
     * @return array
     */
    public static function getScopeAdminIds(bool $with_self = false): array
    {
        $role_ids  = static::getScopeRoleIds();
        $admin_ids = AdminRole::whereIn('role_id', $role_ids)->column('admin_id');
        if ($with_self) {
            $admin_ids[] = get_admin_id();
        }

        return array_unique($admin_ids);
    }

    /**
     * 是否是超级管理员
     *
     * @param int $admin_id
     *
     * @return bool
     */
    public static function isSupperAdmin(int $admin_id = 0): bool
    {
        if ( ! $admin_id) {
            if ( ! $roles = admin('roles')) {
                return false;
            }
        } else {
            $roles = AdminRole::where('admin_id', $admin_id)->column('role_id');
        }
        $rules = Role::whereIn('id', $roles)->column('rules');

        return $rules && in_array('*', $rules);
    }

    /**
     * 根据id获取用户组，返回值为数组
     *
     * @param $adminId
     *
     * @return array
     */
    public static function getGroups($adminId)
    {
        static $groups = [];
        /*if (isset($groups[$adminId])) {
            return $groups[$adminId];
        }*/
        // 执行查询
        $user_groups = Db::name('admin_role')->alias('aga')->join('role ag', 'aga.role_id = ag.id',
            'LEFT')->field('aga.admin_id,aga.role_id,ag.id,ag.pid,ag.name,ag.rules')->where("aga.admin_id='{$adminId}' and ag.status=1")->select();
        if (is_object($user_groups)) {
            $user_groups = $user_groups->toArray();
        }
        $groups[$adminId] = $user_groups ?: [];

        return $groups[$adminId];
    }

    /**
     * 根据用户id，获取对应的权限id
     *
     * @param $uid
     *
     * @return array
     */
    public static function getRuleIds($adminId)
    {
        //读取用户所属用户组
        $groups = self::getGroups($adminId);
        $ids    = []; //保存用户所属用户组设置的所有权限规则id
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);

        return $ids;
    }

}
