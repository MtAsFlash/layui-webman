<?php

namespace plugin\admin\api;

use plugin\admin\api\Menu;

class Install
{

    /**
     * 安装
     *
     * @param $version
     *
     * @return void
     */
    public static function install($version)
    {
        // 导入菜单
        if ($menus = static::getMenus()) {
            Menu::import($menus);
        }
    }

    /**
     * 卸载
     *
     * @param $version
     *
     * @return void
     */
    public static function uninstall($version)
    {
        // 删除菜单
        foreach (static::getMenus() as $menu) {
            Menu::delete($menu['key']);
        }
    }

    /**
     * 更新
     *
     * @param $from_version
     * @param $to_version
     * @param $context
     *
     * @return void
     */
    public static function update($from_version, $to_version, $context = null)
    {
        // 删除不用的菜单
        if (isset($context['previous_menus'])) {
            static::removeUnnecessaryMenus($context['previous_menus']);
        }
        // 导入新菜单
        if ($menus = static::getMenus()) {
            Menu::import($menus);
        }
        // 导入升级sql
        static::getSqls($from_version);
    }

    /**
     * 更新前数据收集等
     *
     * @param $from_version
     * @param $to_version
     *
     * @return array|array[]
     */
    public static function beforeUpdate($from_version, $to_version)
    {
        // 在更新之前获得老菜单，通过context传递给 update
        return ['previous_menus' => static::getMenus()];
    }

    /**
     * 获取菜单
     *
     * @return array|mixed
     */
    public static function getMenus()
    {
        clearstatcache();
        if (is_file($menu_file = __DIR__.'/../config/menu.php')) {
            $menus = include $menu_file;

            return $menus ?: [];
        }

        return [];
    }

    public static function getSqls($from_version)
    {
        clearstatcache();
        $dirPath = str_replace('\\', '/', __DIR__.'/../upgrade/');

        // 获取目录下.sql文件列表
        $sqlFiles = cmf_dir_list($dirPath, 'sql');
        if (empty($sqlFiles)) {
            return;
        }

        // 提取文件名（去除目录路径和扩展名）
        $sqlFileNames = array_map(function ($filePath) use ($dirPath) {
            return str_replace([$dirPath, '.sql'], ['', ''], $filePath);
        }, $sqlFiles);

        // 过滤并保留大于 $from_version 的文件名
        $filteredFileNames = array_filter($sqlFileNames, function ($fileName) use ($from_version) {
            return version_compare($fileName, $from_version, '>');
        });

        // 按版本号升序排序文件名
        usort($filteredFileNames, function ($a, $b) {
            return version_compare($a, $b);
        });
        // 执行符合条件的SQL文件
        foreach ($filteredFileNames as $fileName) {
            static::importSql($dirPath.$fileName.'.sql');
        }
    }

    /**
     * 删除不需要的菜单
     *
     * @param $previous_menus
     *
     * @return void
     */
    public static function removeUnnecessaryMenus($previous_menus)
    {
        $menus_to_remove = array_diff(Menu::column($previous_menus, 'name'), Menu::column(static::getMenus(), 'name'));
        foreach ($menus_to_remove as $name) {
            Menu::delete($name);
        }
    }

    /**
     * 导入sql文件
     *
     * @param $sqlPath
     *
     * @return void
     */
    public static function importSql($sqlPath)
    {
        if (is_file($sqlPath)) {
            $sql        = file_get_contents($sqlPath);
            $sqlRecords = str_ireplace("\r", "\n", $sql);
            $sqlRecords = explode(";\n", $sqlRecords);
            $sqlRecords = str_replace("cmf_", getenv('DB_PREFIX'), $sqlRecords);
            foreach ($sqlRecords as $line) {
                if (empty($line)) {
                    continue;
                }
                try {
                    \think\facade\Db::getPdo()->exec($line);
                } catch (\Throwable $th) {

                }
            }
        }
    }

}
