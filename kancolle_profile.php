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
//************ テンプレ表示
require_once("data/template.php");

print <<<profile
	<!---- ここからモード切替 ---->
	<font size="6"> 提督名：$player[1]</font>
	<br>
	<font size="5">Lv$player[7] [$rank_s[0]]</font>
	<br>
	提督経験値：$player[2]
	<br>
	<form method="get" action="kancolle_profile.php">
	<input type="text" name="co" size="30" maxlength="20" value=$player[9]>
	<input type="submit" value="編集">
	</form>

profile;


/*********	データベース切断	*********/
mysql_close($s);

?>
</html>