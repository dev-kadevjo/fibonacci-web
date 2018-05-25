<?php

namespace Kadevjo\Fibonacci\Installation;

use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class CreateMenu{
  private static $menu;
  public static function buildMenu(){ 
    $menusConfig = json_decode(file_get_contents(__DIR__.'/../config/menus.json'), true);
    static::$menu = Menu::where('name', 'admin')->firstOrFail();
    foreach ($menusConfig as $key => $value) {
      static::item($key, $value);
    }
    return "Complete";
  }

  private static function item($menuItem, $value){
    if(is_array($value)){
      static::subItem($menuItem, $value);
    }else{
      static::saveItem($menuItem, $value, null, $menuItem);
    }
  }

  private static function subItem($menuItem, $sub){
    $subId = static::saveItem($menuItem, '', null);
    foreach ($sub as $key => $value) {
      static::saveItem($key, $value, $subId, $key);
    }
  }

  private static function saveItem($title, $url, $parent=null, $table=null){
    $menuItem = MenuItem::firstOrNew([
      'menu_id' => static::$menu->id,
      'title'   => $title,
      'url'     => $url !== '' ? "/".config('voyager.prefix').$url : $url,
      'route'   => null,
    ]);
    if (!$menuItem->exists) {
      $menuItem->fill([
        'target'     => '_self',
        'icon_class' => '',
        'color'      => null,
        'parent_id'  => $parent,
        'order'      => 99,
      ])->save();
    }
    if(!is_null($table)){ static::ensurePermissionExist($table); }
    return $menuItem->id;
  }

  private static function ensurePermissionExist($table){
    $permissions = [
      Permission::firstOrNew(['key' => 'browse_'.$table, 'table_name' => $table]),
      Permission::firstOrNew(['key' => 'read_'.$table, 'table_name' => $table]),
    ];

    foreach($permissions as $permission){
      if (!$permission->exists) {
        $permission->save();
        $role = Role::where('name', 'admin')->first();
        if (!is_null($role)) {
            $role->permissions()->attach($permission);
        }
      }
    }
  }

}