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
//カード情報の抽出
$c=mysql_query("select havecards.id ,cards.name from havecards join cards
				on havecards.card_id=cards.id where player_id=$P_ID and decknum=1");
//decksからのidとnumの受け取り
$id=$_GET["id"];
$num=$_GET["num"];

/*************	タイトル、画像などの表示	*************/
print <<<disp1
	<html>
		<head>
			<meta http-equiv="Content-Type"
			 content="text/html;charset=shift_JIS">
			<title>艦これ</title>
		</head>
		<body BGCOLOR="lightsteelblue">
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
		<a href="kancolle_profile.php">戦績表示</a>
		<a href="kancolle_cardlist.php">図鑑表示</a>
		<a>アイテム</a>
<!--	<a>模様替え</a> -->
		<a>任務</a>
<!--	<a>アイテム屋</a> -->
		<hr>
		
		</body>
disp1;

/*******	表示内容	*******/

$c=mysql_query("select havecards.decknum, cards.name, havecards.level, havecards.maxhp
				from havecards
				join cards on havecards.card_id=cards.id
				where havecards.player_id=$P_ID");

print <<<disp2
<table border='1'>
<caption>所持艦娘一覧</caption>
<tr>
	<td></td>
	<td>艦名</td>
	<td>Lv</td>
	<td>耐久</td>
</tr>
<tr>
	<td></td>
	<td><a href=".php">はずす</a></td>
	<td></td>
	<td></td>
</tr>
disp2;

$count=0;
while($c_st=mysql_fetch_array($c)){ 
	$count++;
	
	print	"<tr>";
	print	"<td>".$c_st[0]."</td>";
	print	"<td>"."<a href='kancolle_deck.php?count=$count&id=$id&num=$num'>"
			.$c_st[1]."</a>"."</td>";
	print	"<td>".$c_st[2]."</td>";
	print	"<td>".$c_st[3]."</td>";
	print	"</tr>";
	
}
print "</table>";

/*********	データベース切断	*********/
mysql_close($s);

?>
</html>