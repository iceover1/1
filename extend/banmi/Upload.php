<?php
namespace banmi;
use think\facade\Db; 

class Upload
{

      function set_sql($php,$file)
    {
         require_once $php;
         $time= date('Y-m-d-h:i:s', time() );
 
          try {
           $sql_list=trim($_sql);
           $sql_list = explode("//banmi_sql_end",$sql_list);  
             foreach ($sql_list as $key => $value) { 
                 if (strlen($value)>2) {
                  $query = Db::query($value);
                 }  
               }
            } catch (\Exception $e) { 
                 file_put_contents($file.'sql_no_'.$time.'.txt', substr($e, 0, 200));
                 $e=substr($e, 0, 150); 
                 $data['code'] =0; 
                 $data['msg'] ='安装失败,错误已保存到插件目录,sql语句冲突'.$e;  
                 return json_encode($data);
            }
                 $data['code'] =200; 
                 $data['msg'] ='执行完成';  
                return json_encode($data);
           
    }
    
          function set_it($File,$Hostname,$Hostport,$Username,$Password,$Database,$Prefix)
    {
  
        $Config = @file_get_contents($File);
        $back = function ($matches) use ($Hostname, $Hostport, $Username, $Password, $Database, $Prefix) {
            $field = ucfirst($matches[1]);
            $replace = $$field;
            return "'{$matches[1]}'{$matches[2]}=>{$matches[3]}env('database.{$matches[1]}', '{$replace}'),";
        };
        $Config = preg_replace_callback("/'(hostname|database|username|password|hostport|prefix)'(\s+)=>(\s+)env\((.*)\)\,/", $back, $Config);
 
        $result = @file_put_contents($File, $Config);
        return $result;
        
    }
    
    
}
