<?php

/**********	データベース情報などの読み込み	**********/
require_once("data/db_info.php");

/************	データベースへ接続、デーたベース選択	***********/
$s=mysql_connect($SERV,$USER,$PASS) or die("失敗しました");
mysql_select_db($DBNM);

// 提督情報の抽出
$p=mysql_query("select * from players where player_id=$P_ID");
$player=mysql_fetch_array($p);
// カード情報の抽出
$c=mysql_query("select havecards.key_number ,cards.name from havecards join cards 
				on havecards.card_id=cards.id where player_id=$P_ID and decknum=1");
$card=mysql_fetch_array($c);
// ランク一覧の抽出
$rank=mysql_query("select rank from player_ranks where rank_id=$player[10]");
$rank_s=mysql_fetch_array($rank);

//************ テンプレ表示
require_once("data/template.php");

// *******モード切替描画
print <<<modechange
		<!---- ここからモード切替 ---->
		<a>出撃</a>
		<a href="kancolle_deck.php">編成</a>
		<a>補給</a>
		<a>改装</a>
		<a href="kancolle_dock.php">入居</a>
		<a href="kancolle_build.php">工廠</a>
		<hr>
		</body>
modechange;


/*******	内容表示	*****/
print <<<build_disp
	資材を消費してしまいますが建造を行いますか？<br><br>
	<form method="post" action="kancolle_building.php">
	<input type="submit" value="建造する！" style="HEIGHT:50px">
	</form>
build_disp;

/*********	データベース切断	*********/
mysql_close($s);

?>
</html>