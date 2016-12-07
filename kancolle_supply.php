<?php
/*++++++++++++++++++++	開始	++++++++++++++++++++*/
/*--------------------	終了	--------------------*/
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



/*++++++++++++++++++++	前ページ情報取得	++++++++++++++++++++*/
// デッキ番号取得
$d_num=isset($_GET["d_num"])?$_GET["d_num"]:1;
// チェックリストを取得
$supply_list_flg=isset($_GET["supply_list_flg"])?true:null;
$sup_list_st=isset($_GET["supply_list"])?$_GET["supply_list"]:null;
if($sup_list_st!=null)foreach($sup_list_st as $value)$sup_list[]=(int)$value;		// int型に変換
/*--------------------	前ページ情報取得	--------------------*/

/*++++++++++++++++++++	補給処理開始	++++++++++++++++++++*/
if($supply_list_flg!=null){				// 全艦補給チェック時
	// 補給する艦娘のidをデッキから抽出
	$sup_id_list=mysql_query("select id1,id2,id3,id4,id5,id6 from decks
							where player_id='$P_ID' and decknum='$d_num'")
							or die("補給する艦リスト作成失敗".mysql_error());
	$sup_id=mysql_fetch_array($sup_id_list);
	for($i=0; $i<6;$i++){
		if($sup_id[$i]==0)break;	// idが登録されていなければそこから無視
		// 艦娘の艦種と残り燃料,弾薬を特定
		$card_info_list=mysql_query("select cards.type, hc.fuel, hc.bullet 
								from havecards as hc
								join cards on hc.card_id=cards.id
								where hc.player_id='$P_ID' and hc.card_num='$sup_id[$i]'")
								or die("艦娘の情報特定失敗<br>".mysql_error());
		$card_info=mysql_fetch_array($card_info_list);
		// 補給する燃料と弾薬の数を求める
		$sup_fuel=(10-$card_info["fuel"])*$card_info["type"];		// 補給燃料
		$sup_bullet=(10-$card_info["bullet"])*$card_info["type"];	// 補給弾薬
		$fuel = $player["fuel"] - $sup_fuel;			// 現在の燃料から減算
		$bullet = $player["bullet"] - $sup_bullet;		// 現在の弾薬から減算
		// 足りなければ強制終了
		if($fuel<0 && $bullet<0){
			print "<font size='3'>資材が足りませんでした</font>";
			break;
		}
		mysql_query("update players set fuel = $fuel where player_id=$P_ID")
					or die("燃料更新失敗");
		mysql_query("update players set bullet = $bullet where player_id=$P_ID")
					or die("弾薬更新失敗");
		// 艦娘の燃料と弾薬を回復させる
		mysql_query("update havecards set fuel = 10, bullet = 10 
					where player_id='$P_ID' and card_num='$sup_id[$i]'")
					or die("艦娘資材回復更新失敗".mysql_error());
	}
	
	// 最新の提督情報の抽出
	$p=mysql_query("select * from players where player_id=$P_ID");
	$player=mysql_fetch_array($p);
}
else if($sup_list_st!=null){		// 番号チェック時
	// 個別指定があった場合の処理
	foreach($sup_list as $var){		// 配列の要素の数だけループ
		// 艦娘の艦種と残り燃料,弾薬を特定
		$card_info_list=mysql_query("select cards.type, hc.fuel, hc.bullet 
								from havecards as hc
								join cards on hc.card_id=cards.id
								where hc.player_id='$P_ID' and hc.card_num='$var'")
								or die("艦娘の情報特定失敗<br>".mysql_error());
		$card_info=mysql_fetch_array($card_info_list);
		// 補給する燃料と弾薬の数を求める
		$sup_fuel=(10-$card_info["fuel"])*$card_info["type"];		// 補給燃料
		$sup_bullet=(10-$card_info["bullet"])*$card_info["type"];	// 補給弾薬
		$fuel = $player["fuel"] - $sup_fuel;			// 現在の燃料から減算
		$bullet = $player["bullet"] - $sup_bullet;		// 現在の弾薬から減算
		mysql_query("update players set fuel = $fuel where player_id=$P_ID")
					or die("燃料更新失敗");
		mysql_query("update players set bullet = $bullet where player_id=$P_ID")
					or die("弾薬更新失敗");
		// 艦娘の燃料と弾薬を回復させる
		mysql_query("update havecards set fuel = 10, bullet = 10 
					where player_id='$P_ID' and card_num='$var'")
					or die("艦娘資材回復更新失敗".mysql_error());
		
	}
	// 最新の提督情報の抽出
	$p=mysql_query("select * from players where player_id=$P_ID");
	$player=mysql_fetch_array($p);
}
/*--------------------	補給処理終了	--------------------*/

/*++++++++++++++++++++	現在デッキ内にいる艦娘情報抽出を表示開始	++++++++++++++++++++*/
// デッキ情報の抽出
$deck_info=mysql_query("select decknum, name, id1, id2, id3, id4, id5, id6
			from decks where Player_id='$P_ID' and decknum='$d_num'");
$d_info=mysql_fetch_array($deck_info);
// デッキ情報から艦娘idだけを抜き出し
for($i=0; $i<6; $i++){
	$c_id_array[]=$d_info[2+$i];
}
/*--------------------	現在デッキ内にいる艦娘情報抽出を表示終了	--------------------*/

/*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*
	表示開始
*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*+*/
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
/*++++++++++++++++++++	補給表示開始	++++++++++++++++++++*/
print "<form method='get' action='kancolle_supply.php'>";	// データ送信用
print <<<list_title
<table cellpadding="5" border='1'>
<br>
<font size="6">補給したい艦娘を選んでください</font><br>
<br>
<font size="5">デッキ番号：[$d_num]</font>
<br>
<tr>
	<td align='center'><input type='checkbox' name='supply_list_flg' value="all"></td>
	<td></td><!--所属デッキ番号-->
	<td align='center'>艦名</td>
	<td align='center'>Lv</td>
	<td align='center'>燃料</td>
	<td align='center'>弾薬</td>
	<td align='center'>補給燃料</td>
	<td align='center'>補給弾薬</td>
</tr>
list_title;
$count=1;
for($count=1; $count<=6; $count++){
	$c=$count-1;
	$deck_list=mysql_query("select hc.card_num, cards.name, cards.type, hc.level, hc.fuel, hc.bullet
						from havecards as hc
						join cards on hc.card_id=cards.id
						where hc.player_id='$P_ID' and hc.decknum='$d_num' and hc.card_num=$c_id_array[$c]")
						or die("リスト作成失敗<br>".mysql_error());
	$d_list=mysql_fetch_array($deck_list);
	if($d_list!=FALSE){
		print	"<tr>";
		print	"<td align='center'>"."<input type='checkbox' name='supply_list[]' value='$d_list[card_num]'>"."</td>";
		print	"<td align='center'>".$count."</td>";
		print	"<td align='center'>".$d_list["name"]."</td>";
		print	"<td align='center'>".$d_list["level"]."</td>";
		$num_st="";
		for($i=0;$i<$d_list["fuel"];$i++){
			$num_st.="▬";
		}
		print	"<td width='110' align='left'>".$num_st."</td>";
		$num_st="";
		for($i=0;$i<$d_list["bullet"];$i++){
			$num_st.="▬";
		}
		print	"<td width='110' align='left'>".$num_st."</td>";
		print	"<td align='center'>".((10-$d_list["fuel"])*$d_list["type"])."</td>";
		print	"<td align='center'>".((10-$d_list["bullet"])*$d_list["type"])."</td>";
		print	"</tr>";
	}else{
		print	"<tr>";
		print	"<td align='center'>"."</td>";
		print	"<td align='center'>".$count."</td>";
		print	"<td align='center'>"."NO DATA"."</td>";
		print	"<td align='center'>"."</td>";
		print	"<td width='110' align='left'>"."</td>";
		print	"<td width='110' align='left'>"."</td>";
		print	"<td align='center'>"."</td>";
		print	"<td align='center'>"."</td>";
		print	"</tr>";
	}
}
print "</table>";
print "<br><br><input type='submit' value='補給する' style='font-size:25px'>";
print "</form>";
/*--------------------	補給表示終了	--------------------*/


/*********	データベース切断	*********/
mysql_close($s);

?>
</html>