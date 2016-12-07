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
/*		メモ

*/

/*++++++++++++++++++++	前ページ情報取得	++++++++++++++++++++*/
// デッキ番号取得
$d_num=isset($_GET["d_num"])?$_GET["d_num"]:1;
//	外すデッキナンバーの取得
$out_num=isset($_GET["out_num"])?$_GET["out_num"]:null;
//	外す所持艦娘idの取得
$out_id=isset($_GET["out_id"])?$_GET["out_id"]:null;
// デッキ内の交換するリスト番号取得
$list_num=isset($_GET["list_num"])?$_GET["list_num"]:null;	
// 交換先デッキid取得
$cc_id=isset($_GET["cc_id"])?$_GET["cc_id"]:null;
// 交換元デッキid取得
$hc_id=isset($_GET["hc_id"])?$_GET["hc_id"]:null;
// デッキ名取得
$d_name=isset($_GET["d_name"])?htmlspecialchars($_GET["d_name"]):null;
/*--------------------	前ページ情報取得	--------------------*/

/*++++++++++++++++++++	デッキ名変更処理開始	++++++++++++++++++++*/
if($d_name!=null){
	mysql_query("update decks set name = '$d_name'
				where player_id=$P_ID and decknum='$d_num'")
				or die("デッキ名編集失敗しました");
}
/*--------------------	デッキ名変更処理終了	--------------------*/

/*++++++++++++++++++++	外すコマンドあった時の処理開始	++++++++++++++++++++*/
if($out_num!=null){		// 外すコマンドがあれば処理
	// 指定のカラム名取得
	$colum_list = mysql_query("SELECT id1,id2,id3,id4,id5,id6 FROM decks");
	$deck_id = mysql_field_name($colum_list, $out_num-1);
	// 指定したカラムを外した状態へ
	mysql_query("update decks set $deck_id = 0
				where player_id='$P_ID'")
				or die("デッキの所持艦娘情報の外しに失敗".mysql_error());
	// 指定した所持艦娘のdecknumを初期化
	mysql_query("update havecards set decknum = 0
				where Player_id='$P_ID' and card_num='$out_id'")
				or die("艦娘の登録デッキを0に変更失敗".mysql_error());
//	echo "変更予定のカラム名".$deck_id."<br>";
	
	//***************** 繰り上げ処理
	// 最新のデッキ情報を抽出
	$deck_info=mysql_query("select decknum, name, id1, id2, id3, id4, id5, id6
							from decks where Player_id='$P_ID' and decknum='$d_num'");
	$d_info=mysql_fetch_array($deck_info);
	// 指定のナンバーからカラム名取得
	$colum_list = mysql_query("select id1,id2,id3,id4,id5,id6 from decks");
	// デッキ情報から艦娘idだけを配列にする
	for($i=0; $i<6; $i++){
		$c_id[]=$d_info[2+$i];				// 別で配列管理
	}
	//print_r($c_id); // テスト
	for($i=0; $i<5; $i++){
		// 初期化されていたら以下の処理を無視
		if($c_id[$i]!=0)continue;
		// 一個下のデッキidの中身を今のデッキidへ格納
		$now_id_st=mysql_field_name($colum_list, $i);	// 現在の
		$next_id_st=mysql_field_name($colum_list, $i+1);	// 一個先を見るための補正
		mysql_query("update decks set $now_id_st = $next_id_st
					where Player_id='$P_ID'")
					or die("一個下のデッキidを格納失敗".mysql_error());
		$c_id[$i]=$c_id[$i+1];		// 一個下のデッキid格納
		// 一個下のデッキidの中身を0へ
		mysql_query("update decks set $next_id_st = 0
					where Player_id='$P_ID'")
					or die("一個下のデッキidを格納失敗".mysql_error());
		$c_id[$i+1]=0;		// 一個下のデッキid初期化
	}
}
/*--------------------	外すコマンドあった時の処理終了	--------------------*/
/*++++++++++++++++++++	艦娘の追加のみの処理開始	++++++++++++++++++++*/
else if($hc_id==null && $cc_id!=null){	// 交換元idがなく交換先idがあれば処理
	// 所持艦娘の情報抽出
	$havecard_info=mysql_query("select decknum from havecards
							where Player_id='$P_ID' and card_num='$cc_id'")
							or die(mysql_error());
	$hc_info=mysql_fetch_array($havecard_info)or die(mysql_error());
	// 指定のナンバーからカラム名取得
	$colum_list = mysql_query("select id1,id2,id3,id4,id5,id6 from decks");
	$deck_id = mysql_field_name($colum_list, $list_num-1);
	
	// 追加時、同デッキかチェック
	if($d_num!=$hc_info["decknum"]){	// 所属が違えば追加のみの処理へ
//		echo "未所属のデッキからの追加だよ";
		// デッキに艦娘id追加
		mysql_query("update decks set $deck_id = '$cc_id'
					where player_id='$P_ID'")
					or die("デッキに艦娘追加ミス".mysql_error());
		// 艦娘にデッキ情報格納
		mysql_query("update havecards set decknum = '$d_num'
					where player_id='$P_ID' and card_num='$cc_id'")
					or die("艦娘にデッキ情報追加ミス".mysql_error());
	}else{						// 追加と初期化と繰り上げ処理へ
//		echo "同じデッキからの追加だよ、繰り上げもあるよ";
		// 選択した艦娘のデッキナンバー(文字列)を特定
		$deck_info=mysql_query("select decknum, name, id1, id2, id3, id4, id5, id6
			from decks where Player_id='$P_ID' and decknum='$d_num'");
		$d_info=mysql_fetch_array($deck_info);
		// デッキ情報から艦娘idだけを抜き出し
		$deck_number=0;
		for($i=0; $i<6; $i++){
			if($d_info[2+$i]==$cc_id){
				$deck_number=$i;	// 番号格納
			}
			$c_id[]=$d_info[2+$i];				// 別で配列管理
		}
		// デッキナンバー取得
		$deck_id_st = mysql_field_name($colum_list, $deck_number);
		// ↑で特定デッキidを初期化
		mysql_query("update decks set $deck_id_st = 0
					where player_id='$P_ID'")
					or die("デッキidの初期化ミス".mysql_error());
		$c_id[$deck_number]=0;		// 初期化
		// デッキに艦娘id追加
		mysql_query("update decks set $deck_id = '$cc_id'
					where player_id='$P_ID'")
					or die("デッキに艦娘追加ミス".mysql_error());
		$c_id[$list_num-1]=$cc_id;		// 格納
		// 艦娘にデッキ情報格納
		mysql_query("update havecards set decknum = '$d_num'
					where player_id='$P_ID' and card_num='$cc_id'")
					or die("艦娘にデッキ情報追加ミス".mysql_error());
		// 繰り上げ処理
		for($i=0; $i<5; $i++){
			// 初期化されていたら以下の処理を無視
			if($c_id[$i]!=0)continue;
			// 一個下のデッキidの中身を今のデッキidへ格納
			$now_id_st=mysql_field_name($colum_list, $i);	// 現在の
			$next_id_st=mysql_field_name($colum_list, $i+1);	// 一個先を見るための補正
			mysql_query("update decks set $now_id_st = $next_id_st
						where Player_id='$P_ID'")
						or die("一個下のデッキidを格納失敗".mysql_error());
			$c_id[$i]=$c_id[$i+1];		// 一個下のデッキid格納
			// 一個下のデッキidの中身を0へ
			mysql_query("update decks set $next_id_st = 0
						where Player_id='$P_ID'")
						or die("一個下のデッキidを格納失敗".mysql_error());
			$c_id[$i+1]=0;		// 一個下のデッキid初期化
		}
	}
}
/*--------------------	艦娘の追加処理終了	--------------------*/
/*++++++++++++++++++++	艦娘と艦娘の交換開始	++++++++++++++++++++*/
else if($hc_id!=null){		// 交換元艦娘idが取得できていれば処理
	// 交換先艦娘の情報抽出
	$cc_info_list=mysql_query("select decknum from havecards
							where Player_id='$P_ID' and card_num='$cc_id'");
	$cc_info=mysql_fetch_array($cc_info_list);
	// 選択した艦娘のデッキナンバー(文字列)を特定
	$deck_info=mysql_query("select decknum, name, id1, id2, id3, id4, id5, id6
		from decks where Player_id='$P_ID' and decknum='$d_num'");
	$d_info=mysql_fetch_array($deck_info);
	// デッキのカラム名リスト作成
	$colum_list = mysql_query("select id1,id2,id3,id4,id5,id6 from decks");
	// デッキチェック:交換先艦娘が未所属か同デッキかそれ以外(おまけ)か
	if($cc_info["decknum"]==0){				// 未所属の場合
		// 交換元デッキナンバーのカラム名取得
		$deck_id_st = mysql_field_name($colum_list, $list_num-1);
		// 交換元デッキidに交換先デッキidを格納
		mysql_query("update decks set $deck_id_st = '$cc_id'
					where Player_id='$P_ID'")
					or die("交換元デッキidに交換先デッキidを格納失敗".mysql_error());
		// 交換元艦娘のdecknumは初期化
		mysql_query("update havecards set decknum = 0
					where Player_id='$P_ID' and card_num='$hc_id'")
					or die("交換元艦娘のdecknum初期化失敗".mysql_error());
		// 交換先艦娘のdecknumにデッキ番号を格納
		mysql_query("update havecards set decknum = '$d_num'
					where Player_id='$P_ID' and card_num='$cc_id'")
					or die("交換先decknumにデッキ番号を格納失敗".mysql_error());
	}
	else if($cc_info["decknum"]==$d_num){	// 所属が一緒の場合
		// デッキ情報から艦娘idの一致する数字を特定
		$deck_number=0;
		for($i=0; $i<6; $i++){
			if($d_info[2+$i]==$cc_id){
				$deck_number=$i;	// 番号格納
			}
			$c_id[]=$d_info[2+$i];				// 別で配列管理
		}
		// 交換元デッキナンバーのカラム名取得
		$deck_id_st = mysql_field_name($colum_list, $list_num-1);
		// 交換元デッキidに交換先デッキidを格納
		mysql_query("update decks set $deck_id_st = '$cc_id'
					where Player_id='$P_ID'")
					or die("交換元デッキidに交換先デッキidを格納失敗".mysql_error());
		// 交換先デッキidを特定してからそれに交換元デッキidを格納
		
		// デッキナンバー取得
		$deck_id_st = mysql_field_name($colum_list, $deck_number);
		mysql_query("update decks set $deck_id_st = '$hc_id'
					where player_id='$P_ID'")
					or die("デッキidの初期化ミス".mysql_error());
	}
	else{		// それ以外
		//
		// 交換元デッキid($list_numで特定済み)に交換先所持艦娘idを格納
		// 交換先所持艦娘のdecknumにそのデッキ番号を格納
		// 交換元所持艦娘のdecknumに交換先所持艦娘のdecknumを特定し格納
		// 交換先所持艦娘が所属していたdecknumを使いどのデッキidに属していたかを特定
		// 特定した↑のデッキidに交換元所持艦娘idを格納
	}
}
/*--------------------	艦娘と艦娘の交換終了	--------------------*/

/*++++++++++++++++++++	指定デッキのカードリスト表示開始	++++++++++++++++++++*/
// デッキ情報の抽出
$deck_info=mysql_query("select decknum, name, id1, id2, id3, id4, id5, id6
			from decks where Player_id='$P_ID' and decknum='$d_num'");
$d_info=mysql_fetch_array($deck_info);
// デッキ情報から艦娘idだけを抜き出し
for($i=0; $i<6; $i++){
	$c_id_array[]=$d_info[2+$i];
}
print <<<deck_name
	
	<form method="get" action="kancolle_deck.php">
	<font size="6">[$d_num]艦隊名：</font><input type="text" name="d_name" maxlength="10" style="font-size:25px;width:240px;height:35px;" value=$d_info[name]>
	<input type="submit" style="font-size:23px;width:70px;height:35px;" value="編集">
deck_name;

print <<<T_Disp
<br><br>
<table cellpadding="5" border='1'>
<tr>
	<td></td>
	<td align='center'>艦種</td>
	<td align='center'>Lv</td>
	<td align='center'>艦名</td>
	<td align='center'>耐久</td>
	<td></td>
	<td></td>
</tr>
T_Disp;
for($count=1, $flg=false; $count<=6; $count++){ // 一覧をループで表示
	// 所持艦娘tbとカードtbから情報を抽出
	$c=$count-1;
	$deck_card_info=mysql_query("select hc.card_num, cards.type, hc.level, cards.name, hc.hp, hc.maxhp
					from havecards as hc
					join cards on hc.card_id=cards.id
					where hc.player_id=$P_ID and card_num='$c_id_array[$c]'")
					or die("所持カードの情報抽出失敗".mysql_error());
	$c_info=mysql_fetch_array($deck_card_info);
	if($c_info!=FALSE){
		print	"<tr>";
		print	"<td align='center'>".$count."</td>";
		print	"<td align='center'>".$c_info["type"]."</td>";
		print	"<td align='center'>".$c_info["level"]."</td>";
		print	"<td align='center'>".$c_info["name"]."</td>";
		print	"<td align='center'>".$c_info["hp"]."/".$c_info["maxhp"]."</td>";
		print	"<td align='center'>"."<a href='kancolle_deck_select.php?d_num=$d_num&list_num=$count&hc_id=$c_info[0]'>"."変更"."</a>"."</td>";
		if($count==1 && $c_id_array[$c+1]==0){
			print	"<td width='35' align='center'>"."</td>";
		}else{
			print	"<td width='35' align='center'>"."<a href='kancolle_deck.php?d_num=$d_num&out_num=$count&out_id=$c_info[0]'>"."外す"."</a>"."</td>";
		}
		print	"</tr>";
		flush();	// 表示させやすくするため
	}else if($flg==false){
		print	"<tr>";
		print	"<td align='center'>".$count."</td>";
		print	"<td align='center'>"."</td>";
		print	"<td align='center'>"."</td>";
		print	"<td align='center'>"."</td>";
		print	"<td align='center'>"."</td>";
		print	"<td align='center'>"."<a href='kancolle_deck_select.php?d_num=$d_num&list_num=$count'>"."追加"."</a>"."</td>";
		print	"<td width='35' align='center'>"."</td>";
		print	"</tr>";
		$flg=true;
	}else{
		print	"<tr>";
		print	"<td align='center'>".$count."</td>";
		print	"<td align='center'>"."</td>";
		print	"<td align='center'>"."</td>";
		print	"<td align='center'>"."</td>";
		print	"<td align='center'>"."</td>";
		print	"<td align='center'>"."</td>";
		print	"<td align='center'>".""."</td>";
		print	"</tr>";
	}
}
print "</table>";
/*--------------------	指定デッキのカードリスト表示終了	--------------------*/




/*********	データベース切断	*********/
mysql_close($s);

?>
</html>