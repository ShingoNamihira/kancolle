<?php

/**********	データベース情報などの読み込み	**********/
require_once("data/db_info.php");

/************	データベースへ接続、デーたベース選択	***********/
$s=mysql_connect($SERV,$USER,$PASS) or die("失敗しました");
mysql_select_db($DBNM);

// 提督情報の抽出
$p=mysql_query("select fuel,bullet,steel,bauxite 
				from players where player_id=$P_ID");
$player=mysql_fetch_array($p);
// カード情報の抽出
$c=mysql_query("select * from cards");
while($value=mysql_fetch_array($c,MYSQL_NUM)){
	$card[]=$value;
}

// 所持艦娘情報の抽出
$hc=mysql_query("select card_id from havecards where player_id=$P_ID");
/*************		建造処理開始	*****************/
// ＊プレイヤーの所持している艦娘の数
$hc_num=mysql_num_rows($hc)+1;
// ランダムで図鑑Noの中から一つ数値を生成
$ran=rand(0, count($card)-1);
// プレイヤーの資材を減らす
$use_material = 100;
$fuel = $player[0] - $use_material;
$bullet = $player[1] - $use_material;
$steel = $player[2] - $use_material;
$bauxite = $player[3] - $use_material;
mysql_query("update players set fuel = $fuel where player_id=$P_ID")
				or die("更新失敗");
mysql_query("update players set bullet = $bullet where player_id=$P_ID")
				or die("更新失敗");
mysql_query("update players set steel = $steel where player_id=$P_ID")
				or die("更新失敗");
mysql_query("update players set bauxite = $bauxite where player_id=$P_ID")
				or die("更新失敗");

$c_hp=$card[$ran][3];
$c_status=$card[$ran][4];

// ランダム生成した図鑑Noの艦娘をhavecardsにinsert
mysql_query("
			insert into havecards ( player_id, card_num, card_id, hp, maxhp) 
				values( $P_ID, $hc_num, $ran+1, $c_hp, $c_hp)
			")or die("艦娘追加失敗<br>".mysql_error());

/*************		建造処理終了	*****************/

/*************	タイトル、画像などの表示	*************/
print <<<disp1
	<html>
		<head>
			<meta http-equiv="Content-Type"
			 content="text/html;charset=utf-8">
			<title>艦これ</title>
		</head>
		<body BGCOLOR="lightsteelblue">
		</body>
disp1;

$c_name=$card[$ran][1];		// キャラ名格納
/*******	内容表示	*****/
print <<<disp2
	<font size="6">
	ゲットした艦娘は<br>
	</font>
	<font size="7">
	$c_name
	</font>
	<font size="6">
	です！<br>
	</font>
	
	<a href="kancolle_build.php">戻る</a>
disp2;

/*********	データベース切断	*********/
mysql_close($s);

?>
</html>