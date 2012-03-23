<?php

require_once("../bootstrap.php");
require_once(Sys."/ACLControl.php");
include_once '../PPL/bin/Page.php';
require_once(Sys."/ACLInit.php");
$ui = new ACLControl();
$req = PorG();
$ui->CA_view();

$act = $req['act'];
$id = intval($req['id']);
$fields = array('title', 'href', 'cate', 'desc', );
$pagename = $_SERVER['PHP_SELF'];
if(empty($act)) get_list();
if(in_array($act, array('add', 'list', 'edit', )))	call_user_func($act);
else exit('非法请求');
//添加
function add($field) {			
	$fileds = $values = '';
	$now = time();
	foreach($field as $v) {
		$fields .= "`{$v}`, ";
		$value = addslashes($v);
		$values .= "'{$value}', ";
	}
	$fields = rtrim($fields, ', ');
	$values = rtrim($values, ', ');
	$sql = "INSERT INTO  `www`.`headnews` ({$fields}) VALUES ({$values});";

	MySQL::Insert($sql, GetPB('w'));
}
//显示列表
function get_list(){
	$sql = "SELECT * FROM `www`.`headnews`  LIMIT 8;";
	$res = MySQL::FetchArray($sql, GetPB());
		
	if(!is_array($res) || empty($res)) return false;
	$cont = "<tr bgcolor='#FBFCE2'><td width='5%' align='center'>标题</td><td width='5%' align='center'>链接</td><td width='5%' align='center'>类别</td><td width='5%' align='center'>描述</td></tr>";
	foreach($res as $k=>$v) {
		$color = "#FFFFFF";
		$k%2==1 && $color = "#F9FCEF";
		$cont .= <<<HTML
		<tr bgcolor="$color" align="center">
		<td width='10%' height='34' align='center'>{$v['title']}</td><td width='10%' height='34' align='center'>{$v['href']}</td><td width='10%' height='34' align='center'>{$v['cate']}</td><td width='10%' height='34' align='center'>{$v['desc']}</td>
		</tr>
HTML
	}
	return $cont;
}
//编辑
function edit($id, ) {
	$req = PorG();
	$data = $value = '';
	$now = time();
	foreach($fields as $v) {	
		$value = addslashes($req[$v]);	
		$data .= "`{$v}`='{$value}', ";
	}
	$data = rtrim($data, ', ');
	$sql = "UPDATE `www`.`headnews` SET {$data} WHERE `id`='{$id}';";

	MySQL::Update($sql, GetPB('w'));
}
