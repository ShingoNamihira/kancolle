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
		提督名 : $player[name]  
		Lv.$player[level] [$rank_s[rank]]
		<br>
		<!-- 資材 -->
		燃料：$player[3] 鋼材：$player[5] <br>
		弾薬：$player[4] ボーキ：$player[6]
		
		<br>
		<hr>
		<a href="kancolle_profile.php">戦績表示</a>
		<a href="kancolle_cardlist.php">図鑑表示</a>
		<a>アイテム</a>
<!--	<a>模様替え</a> -->
		<a>任務</a>
<!--	<a>アイテム屋</a> -->
		<hr>
		
		<!---- ここからモード切替 ---->
		<a>出撃</a>
		<a href="kancolle_hclist.php">編成</a>
		<a>補給</a>
		<a>改装</a>
		<a href="kancolle_dock.php">入居</a>
		<a href="kancolle_build.php">工廠</a>
		<hr>
		</body>
disp1;

/*******	内容表示	*****/
print <<<disp2
<!--******	  波平追加	*********-->
	秘書官：$card[0] $card[1]
<!--******	  波平追加終了	*********-->
	
disp2;

/*********	データベース切断	*********/
mysql_close($s);

?>
</html>