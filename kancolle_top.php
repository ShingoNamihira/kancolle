<?php

/**********	データベース情報などの読み込み	**********/
require_once("data/db_info.php");

/************	データベースへ接続、データベース選択	***********/
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
		<a href="kancolle_supply.php">補給</a>
		<a>改装</a>
		<a href="kancolle_dock.php">入居</a>
		<a href="kancolle_build.php">工廠</a>
		<hr>
		</body>
modechange;

/*******	秘書官表示	*****/
// サブクエリを利用して艦娘情報を取得
$secret_list=mysql_query("select cards.name
				from havecards as hc
				join cards on hc.card_id=cards.id
				where hc.player_id='$P_ID' and hc.card_num=(select id1 from decks where player_id='$P_ID')")
				or die("所持艦娘リスト作成失敗<br>".mysql_error());
$secret=mysql_fetch_array($secret_list);
print <<<secretary
	<font size="6">秘書官:</font>
	<font size="7">$secret[name]</font>
	<br>
secretary;

/*********	データベース切断	*********/
mysql_close($s);

?>
</html>














