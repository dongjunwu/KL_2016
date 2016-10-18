<?php
/**
 * Created by PhpStorm.
 * User: whian
 * Date: 2016/9/1
 * Time: 10:24
 */
require "Apps/srv/common.php";
require "Apps/srv/MysqlClass.php";
$UID = $_SESSION["USER_ID"];
$key = $_POST["key"];
if(!$_SESSION["json_base_info"]){//用户未登录
    if($key){
        ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727;)');
        $rm_url = "http://login.k12china.com/login/v2/loginInfo.jsp?key=".$key."&_dc=".time().mt_rand(10000,99999);
        $rm_dt = mb_convert_encoding(trim(file_get_contents($rm_url)),"UTF-8","GBK");
        $rm_arr = json_decode($rm_dt,true);
        if(is_array($rm_arr))
        {
            $sql = new DbMysql();
            $UID = $rm_arr['userName'];
            $query = "select * from user where userid='$UID'";
            $rst = $sql->DataQuery($query) or die(mb_convert_encoding("数据库操作出错0","UTF-8","GBK"));
            $row = $sql->DataFetchArray1($rst);
            if(!$row)
            {
                $nick = $UID;
                $cpoint = $vip = 0;
                $rname = mb_convert_encoding($rm_arr['realName'],"GBK","UTF-8");
                $u_type = $rm_arr["userType"];
                $sl_info = mb_convert_encoding($rm_arr["schoolName"].";".$rm_arr["className"],"GBK","UTF-8");
                $auth = $rm_arr['authenticate']=="true"?1:0;
                $psw = $_POST["pwkey"];
                $lastime = date("Y-m-d H:i:s");
                $query = "insert into user (userid,Psw,rname,school_info,classid,grade,schoolid,cityid,sessionkey,User_type,auth,lastime) values
               ('$UID','$psw','$rname','$sl_info','".$rm_arr['classId']."','".$rm_arr['grade']."','".$rm_arr['schoolId']."','".$rm_arr['city']."','$key',$u_type,$auth,'$lastime')";
                $sql->DataQuery($query);
            }
            else
            {
                $nick = $row['nick'];
                $cpoint = $row['cpoint'];
                $vip = $row['vip'];
                $rname = $row['rname'];
            }
            $_SESSION["USER_ID"] = $UID;
            $_SESSION["NICK_NAME"] = $nick;
        }
        else die($rm_url."--".$rm_dt);
    }
    else die("keyErr");
    $out = '{"uid":"'.$UID.'","rname":"'.$rname.'","nick":"'.$nick.'","vip":"'.$vip.'","cpoint":"'.$cpoint.'""}';
    $_SESSION["json_base_info"] = $out;
}
else  $out = $_SESSION["json_base_info"];
echo json_encode(mb_convert_encoding($out,"UTF-8","GBK"));

//echo $rm_url;


/*
$fn = "cache/info".$Gid;//有多少人在玩/最高记录
if(file_exists($fn)) $ginfo = file_get_contents($fn);
else{
    $ginfo = '"num":0,"times":0,"topList":""';
    if($gamesArr[$Gid])
    {
        $_arr = array();
        foreach($gamesArr[$Gid] as $k) array_push($_arr,'{"num":0,"times":0,"topList":""}');
        $ginfo .= ',"mods":['.implode(",",$_arr).']';
    }
    file_put_contents($fn,$ginfo);
}

$key = "mygame_".$Gid;
if($_SESSION[$key]) $_arr = explode(',',$_SESSION[$key]);
else{
    $sql = new DbMysql();
    $db = $sql->game_dbs[$Gid];
    $query = "select * from ".$db." where uid='$UID'";
    $rst = $sql->DataQuery($query);
    $rows = $sql->DataFetchArray($rst);
    $_arr = array();
    foreach($gamesArr[$Gid] as $k) $_arr[$k] = "";
    if($rows)
    foreach($rows as $r) $_arr[$r['gmod']] = $r['score_log'];
    else $_SESSION["new_user_for_".$Gid] = 1;
    $_SESSION[$key] = implode(',',$_arr);
}

echo json_encode(json_decode("{".$ginfo."}"));*/