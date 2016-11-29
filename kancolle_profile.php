<?php

/**********	データベース情報などの読み込み	**********/
require_once("data/db_info.php");

/************	データベースへ接続、デーたベース選択	***********/
$s=mysql_connect($SERV,$USER,$PASS) or die("失敗しました");
mysql_select_db($DBNM);

// 提督情報の抽出
$p=mysql_query("select * from players where player_id=$P_ID");
$player=mysql_fetch_array($p);
// ランク一覧の抽出
$rank=mysql_query("select rank from player_ranks where rank_id=$player[10]");
$rank_s=mysql_fetch_array($rank);

/*********	入力したコメを取得してタグを削除	*********/
$co_d=isset($_GET["co"])?htmlspecialchars($_GET["co"]):null;

/*********  コメにデータがあればコメントを更新	*********/
if($co_d<>""){
	mysql_query("update players set comment = '$co_d' where player_id=$P_ID")
				or die("編集失敗しました");
	$player[9]=$co_d;
}
/*************	タイトル、画像などの表示	*************/
print <<<disp1
	<html>
		<head>
			<meta http-equiv="Content-Type"
			 content="text/html;charset=shift_JIS">
			<title>艦これ</title>
		</head>
		<body BGCOLOR="lightsteelblue">
		<!--******	  UI	*********-->
		<font size="7"><a href="kancolle_top.php">母港</a></font>
		<!-- プレイヤーネーム -->
		提督名 : $player[1]  
		Lv.$player[7] [$rank_s[0]]
		<br>
		燃料：$player[3] 
		弾薬：$player[4] 
		鋼材：$player[5] 
		ボーキ：$player[6]
		
		<br>
		<hr>
		<a>戦績表示</a>
		<a href="kancolle_cardlist.php">図鑑表示</a>
		<a>アイテム</a>
<!--	<a>模様替え</a> -->
		<a>任務</a>
<!--	<a>アイテム屋</a> -->
		<hr>
		
		</body>
		
disp1;

print <<<disp2
	<!---- ここからモード切替 ---->
	<font size="6"> 提督名：$player[1]</font>
	<br>
	<font size="5">Lv$player[7] [$rank_s[0]]</font>
	<br>
	提督経験値：$player[2]
	<br>
	<form method="get" action="kancolle_profile.php">
	<input type="text" name="co" size="20" maxlength="20" value=$player[9]>
	<input type="submit" value="編集">
	</form>

disp2;


/*********	データベース切断	*********/
mysql_close($s);

?>
</html>