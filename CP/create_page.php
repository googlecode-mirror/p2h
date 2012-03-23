<?php
CP::run();

class CP {
	static $info = array();
	
	public function run() {
		self::$info = array(
				'pagename'=>'hotnews_manage.php',
				'table'=>"`www`.`headnews`",
				'fields'=>array(
					'title'=>'标题', 
					'href'=>'链接', 
					'cate'=>'类别', 
					'desc'=>'描述',
				),
				'acts'=>array(
					'add', 'list', 'edit',
				),
				'dir'=>'./create',
		);
		if(!is_dir(self::$info['dir']))	mkdir(self::$info['dir'], 0777);
		self::write('<?php'.PHP_EOL);
		
		$acts = '';
		foreach(self::$info['acts'] as $v) {
			$acts .= "'{$v}', ";
		}
		rtrim($acts, ', ');
		
		$fields = '';
		foreach(self::$info['fields'] as $k=>$v) {
			$fields .= "'{$k}', ";
		}
		rtrim($fields, ', ');
		
		$init = <<<HEREDOC
require_once("../bootstrap.php");
require_once(Sys."/ACLControl.php");
include_once '../PPL/bin/Page.php';
require_once(Sys."/ACLInit.php");
\$ui = new ACLControl();
\$req = PorG();
\$ui->CA_view();

\$act = \$req['act'];
\$id = intval(\$req['id']);
\$fields = array({$fields});
\$pagename = \$_SERVER['PHP_SELF'];
if(empty(\$act)) get_list();
if(in_array(\$act, array({$acts})))	call_user_func(\$act);
else exit('非法请求');
HEREDOC;

		self::write($init);
		self::create_function(self::$info['acts']);
	}
	
	public function create_function($act) {
		foreach($act as $v) {
			$cont = call_user_func('self::action_'.$v);
			self::write($cont);
		}
	}
	
	public function action_list() {
	$table = self::$info['table'];
	foreach(self::$info['fields'] as $k=>$v) {
		$html .= "<td width='10%' height='34' align='center'>{\$v['{$k}']}</td>";
		$tbody .= "<td width='5%' align='center'>{$v}</td>";
	}

$con = <<<EOF
//显示列表
function get_list(){
	\$sql = "SELECT * FROM {$table}  LIMIT 8;";
	\$res = MySQL::FetchArray(\$sql, GetPB());
		
	if(!is_array(\$res) || empty(\$res)) return false;
	\$cont = "<tr bgcolor='#FBFCE2'>{$tbody}</tr>";
	foreach(\$res as \$k=>\$v) {
		\$color = "#FFFFFF";
		\$k%2==1 && \$color = "#F9FCEF";
		\$cont .= <<<HTML
		<tr bgcolor="\$color" align="center">
		{$html}
		</tr>
HTML
	}
	return \$cont;
}
EOF;
		return $con; 
	}
	
	public function action_edit() {
	$table = self::$info['table'];
$con = <<<HTML
//编辑
function edit(\$id, $fields) {
	\$req = PorG();
	\$data = \$value = '';
	\$now = time();
	foreach(\$fields as \$v) {	
		\$value = addslashes(\$req[\$v]);	
		\$data .= "`{\$v}`='{\$value}', ";
	}
	\$data = rtrim(\$data, ', ');
	\$sql = "UPDATE {$table} SET {\$data} WHERE `id`='{\$id}';";

	MySQL::Update(\$sql, GetPB('w'));
}
HTML;

	return $con;
	}
	
	public function action_add() {
$table = self::$info['table'];
$con = <<<EOL
//添加
function add(\$field) {			
	\$fileds = \$values = '';
	\$now = time();
	foreach(\$field as \$v) {
		\$fields .= "`{\$v}`, ";
		\$value = addslashes(\$v);
		\$values .= "'{\$value}', ";
	}
	\$fields = rtrim(\$fields, ', ');
	\$values = rtrim(\$values, ', ');
	\$sql = "INSERT INTO  {$table} ({\$fields}) VALUES ({\$values});";

	MySQL::Insert(\$sql, GetPB('w'));
}
EOL;
		return $con;
	}
	
	protected function write($con) {
		return file_put_contents(self::$info['dir'].'/'.self::$info['pagename'],  $con.PHP_EOL, FILE_APPEND);
	}
	
}
?>