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
$c=mysql_query("select havecards.id ,cards.name from havecards join cards 
				on havecards.card_id=cards.id where player_id=$P_ID and decknum=1");

/************	艦娘を入れ替えていたら更新	***********/
$chengeID = isset($_GET["count"])?$_GET["count"]:null;
$id = isset($_GET["id"])?$_GET["id"]:null;
$num = isset($_GET["num"])?$_GET["num"]:null;
if(!is_null($chengeID) and $chengeID != $id){
	
	mysql_query("update decks set id$num=$chengeID where player_id=$P_ID");

	mysql_query("update havecards set decknum=0 
				where player_id=$P_ID and card_num=$id");
	mysql_query("update havecards set decknum=1
				where player_id=$P_ID and card_num=$chengeID");
}

$jhd=mysql_query("select * from decks where player_id=$P_ID");
$jhdeck=mysql_fetch_array($jhd);

$id1=$jhdeck[3];
$id2=$jhdeck[4];
$id3=$jhdeck[5];
$id4=$jhdeck[6];
$id5=$jhdeck[7];
$id6=$jhdeck[8];

for($i = 1; $i<7;$i++){
//print($i);
$id = $jhdeck[2+$i];
$jch=mysql_query("select cards.id, havecards.card_num, cards.name, havecards.decknum from havecards
				  join cards on havecards.card_id=cards.id
				  where havecards.card_num=$id and havecards.player_id=$P_ID");
${"jchcard".$i}=mysql_fetch_array($jch);
}


//$p_name=var_dump($player[1]);
//$p_name=var_dump($player);

// $player[10]はランク

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
		艦隊名:$jhdeck[2] <br>
		<table border="1">
		<tr>
			<td>番号</td><td>名前</td><td>所持ID</td>
		</tr>
		<tr>
			<td>1</td><td><a href="kancolle_hclist.php?id=$id1&num=1">$jchcard1[2]</a></td><td>$id1</td>
		</tr>
		<tr>
			<td>2</td><td><a href="kancolle_hclist.php?id=$id2&num=2">$jchcard2[2]</a></td><td>$id2</td>
		</tr>
		<tr>
			<td>3</td><td><a href="kancolle_hclist.php?id=$id3&num=3">$jchcard3[2]</a></td><td>$id3</td>
		</tr>
		<tr>
			<td>4</td><td><a href="kancolle_hclist.php?id=$id4&num=4">$jchcard4[2]</a></td><td>$id4</td>
		</tr>
		<tr>
			<td>5</td><td><a href="kancolle_hclist.php?id=$id5&num=5">$jchcard5[2]</a></td><td>$id5</td>
		</tr>
		<tr>
			<td>6</td><td><a href="kancolle_hclist.php?id=$id6&num=6">$jchcard6[2]</a></td><td>$id6</td>
		</tr>
		</table>
		
disp1;

/*********	データベース切断	*********/
mysql_close($s);

?>
</html>