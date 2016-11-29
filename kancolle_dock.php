<?php

/**********	データベース情報などの読み込み	**********/
require_once("data/db_info.php");

/************	データベースへ接続、デーたベース選択	***********/
$s=mysql_connect($SERV,$USER,$PASS) or die("失敗しました");
mysql_select_db($DBNM);

// 入渠コマンド格納
$mc_id=isset($_GET["hc_id"])?(int)$_GET["hc_id"]:NULL;

// 提督情報の抽出
$p=mysql_query("select * from players where player_id=$P_ID");
$player=mysql_fetch_array($p);
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
		提督名 : $player[1]  
		Lv.$player[7] [$rank_s[0]]
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
		<a>入居</a>
		<a href="kancolle_build.php">工廠</a>
		<hr>
		</body>
disp1;

/***********	入渠コマンドあり	**********/
if($mc_id!=""){
	
	// 入渠テーブルのcard_idにコマンドをアプデ
	mysql_query("update docks set havecard_id = $mc_id
				where player_id='$P_ID'")
				or die("IDアプデ失敗".mysql_error());
	// 入渠テーブルのflgをアプデ
	mysql_query("update docks set flg = true
				where player_id='$P_ID'")
				or die("flgアプデ失敗".mysql_error());
	// 入居中カードの情報抽出
	$havecard=mysql_query("select hp,maxhp 
						from havecards 
						where player_id='$P_ID' and card_num=$mc_id
						")
						or die("hc情報抽出失敗".mysql_error());
	$hc=mysql_fetch_array($havecard);
	$min=$hc["hp"]-$hc["maxhp"];		// 入渠時間算出
//	date_default_timezone_set('Asia/Tokyo');
	$future=date("Y/m/d/ H:i:s", strtotime("+$min minute"));
	$furure = new DateTime();
	$future->setTimeZone(new DateTimeZone('Asia/Tokyo'));
	// 入渠テーブルのmove_timeに入渠終了の時刻をアプデ**********
	mysql_query("update docks set move_time = '$furure'
				where player_id='$P_ID'")
				or die(mysql_error());
	
	// 指定の所持艦娘を入渠状態(1)へ
	mysql_query("update havecards set state = 1 
				where player_id='$P_ID' and card_num='$mc_id'")
				or die(mysql_error());
}
/***********	入渠コマンドなし	**********/
else{
	// 入居情報取得
	$dock=mysql_query("select flg,havecard_id,move_time 
						from docks where player_id='$P_ID'")
						or die("d情報抽出失敗".mysql_error());
	$d=mysql_fetch_array($dock);
	
	switch($d["flg"]){
	case false:		// 入渠中の艦娘がいない
print <<<NOT
		現在入居中の艦娘はいらっしゃいません('◇')ゞ<br><br><br>
NOT;
print <<<WHAT
		傷ついている艦娘を入渠させますか？<br><br>
		<form method="post" action="kancolle_dock_select.php">
		<input type="submit" value="入渠させる！" style="HEIGHT:30px">
		</from>
WHAT;
		break;
	case true:			// 入渠中の艦娘あり
	// 入渠終了判定
	// 入居情報取得
	$now = new DateTime();
	$now->setTimeZone(new DateTimeZone('Asia/Tokyo'));
	$f_time = new DateTime();
	$f_time=$d["move_time"]?$d["move_time"]->format('Y-m-d H:i:s'):NULL;
	$n_time=$now->format('Y-m-d H:i:s');
	// 入渠時間を経過済みだったら
	if($f_time!=NULL and strtotime($f_time)<=strtotime($n_time)){
		// 指定の所持艦娘を通常状態(0)へ
		mysql_query("update havecards set state = 0 
					where player_id='$P_ID'
					and card_num = $d[havecard_id]")
					or die("普通状態へできなかった".mysql_error());
		// 入渠テーブルのflgをアプデ
		mysql_query("update docks set flg = true
					where player_id='$P_ID'")
					or die("flgアプデ失敗".mysql_error());
	}
		// 入居中カードの情報抽出
		$havecard=mysql_query("select card_id,level,hp,maxhp 
							from havecards 
							where player_id='$P_ID' and card_num=$d[havecard_id]
							")
							or die("hc情報抽出失敗".mysql_error());

		$hc=mysql_fetch_array($havecard);
		$card=mysql_query("select type,name from cards
							where id=$hc[card_id]")
							or die("c情報抽出失敗".mysql_error());
		$c=mysql_fetch_array($card);
		// 入居中情報表示開始
		$time=new DateTime(strtotime(strtotime($n_time)-strtotime($f_time)));
		$time->setTimeZone(new DateTimeZone('Asia/Tokyo'));
print <<<TABLE1
		<table border='1'>
		<caption>入渠中艦娘</caption>
		<tr>
			<td>艦種</td>
			<td>艦名</td>
			<td>Ｌｖ</td>
			<td>耐久</td>
			<tb>残り時間</tb>
		</tr>
TABLE1;
		print "<tr>";
		print "<td>".$c["type"]."</td>";
		print "<td>".$c["name"]."</td>";
		print "<td>".$hc["level"]."</td>";
		print "<td>".$hc["hp"]."/".$hc["maxhp"]."</td>";
		print "<td>".$time->format("H:m")."</td>";
		print "</tr>";
		print "</table>";
		break;
	}
}
/*********	データベース切断	*********/
mysql_close($s);

?>
</html>