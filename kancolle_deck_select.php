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

/*++++++++++++++++++++	前ページ情報取得	++++++++++++++++++++*/
$d_num=$_GET["d_num"];			// デッキ番号
$list_num=$_GET["list_num"];	// デッキ内の交換するリスト番号取得
// デッキ内の交換するリスト番号取得(isset()で変数が格納されているかチェックしている)
$hc_id=isset($_GET["hc_id"])?$_GET["hc_id"]:null;
/*--------------------	前ページ情報取得	--------------------*/

/*++++++++++++++++++++	所持カードの表示(送信リンク付き)	++++++++++++++++++++*/
if($hc_id!=null){
	// 同名カードが入らないように交換対象以外のデッキに所属中カードは選べ無いようにする
	$notin_id_list=mysql_query("select card_id,card_num from havecards
								where Player_id='$P_ID' and decknum='$d_num'")
								or die("同名カード追加防止リスト作成失敗".mysql_error());
//	var_dump($notin_id_list);
	// 件数が1だけなら
	if(mysql_num_rows($notin_id_list)>1){
		// 配列作成
		while($ni_id=mysql_fetch_array($notin_id_list)){
			if($hc_id!=$ni_id["card_num"]){
				$ni_list[]=$ni_id["card_id"];
			}
		}//print_r($ni_list);print "<br>";
		// 配列からリストを文字列に変換
		$ni_list_st=implode(",", $ni_list);
		// 所持カードリストの作成
		$hc_list=mysql_query("select hc.card_num, hc.decknum, cards.type, hc.level, cards.name, hc.hp, hc.maxhp
						from havecards as hc
						join cards on hc.card_id=cards.id
						where hc.player_id='$P_ID' and hc.state=0 and hc.card_num!='$hc_id' and
						( hc.decknum='$d_num' or hc.card_id not in($ni_list_st) )")
						or die("所持艦娘リスト作成失敗<br>".mysql_error());
	}else{
		// 所持カードリストの作成
		$hc_list=mysql_query("select hc.card_num, hc.decknum, cards.type, hc.level, cards.name, hc.hp, hc.maxhp
						from havecards as hc
						join cards on hc.card_id=cards.id
						where hc.player_id='$P_ID' and hc.state=0 and hc.card_num!='$hc_id'")
						or die("1以下、所持艦娘リスト作成失敗<br>".mysql_error());
	}
	
}else{
	if($list_num!=1){
		// 追加処理なら選択したナンバーの一個前の艦娘を選択対象から外す
		// カラム名取得
		$colum_list = mysql_query("select id1,id2,id3,id4,id5,id6 from decks");
		$deck_id = mysql_field_name($colum_list, $list_num-2);
		// 艦娘idの特定
		$card_id_array=mysql_query("select $deck_id from decks where Player_id='$P_ID'")
							or die("一個前の艦娘idの特定失敗".mysql_error());
		$not_target_id=mysql_fetch_array($card_id_array)or die("id格納失敗".mysql_error());
		$nt_id=$not_target_id[$deck_id];
		// 同名カードが入らないように交換対象以外のデッキに所属中カードは選べ無いようにする
		$notin_id_list=mysql_query("select card_id from havecards
									where Player_id='$P_ID' and decknum='$d_num'")
									or die("同名カード追加防止リスト作成失敗".mysql_error());
		// 配列作成
		while($ni_id=mysql_fetch_array($notin_id_list)){
			$ni_list[]=$ni_id["card_id"];
		}print_r($ni_list);print "<br>";
		
		// 配列からリストを文字列に変換
		$ni_list_st=implode(",", $ni_list);
		// 所持カードリストの作成
		$hc_list=mysql_query("select hc.card_num, hc.decknum, cards.type, hc.level, cards.name, hc.hp, hc.maxhp
						from havecards as hc
						join cards on hc.card_id=cards.id
						where hc.player_id='$P_ID' and hc.state=0 and hc.card_num!='$nt_id' and
						( hc.decknum='$d_num' or hc.card_id not in($ni_list_st) )")
						or die("所持艦娘リスト作成失敗<br>".mysql_error());
	}else{
		// 所持カードリストの作成
		$hc_list=mysql_query("select hc.card_num, hc.decknum, cards.type, hc.level, cards.name, hc.hp, hc.maxhp
						from havecards as hc
						join cards on hc.card_id=cards.id
						where hc.player_id='$P_ID' and hc.state=0")
						or die("所持艦娘リスト作成失敗<br>".mysql_error());
	}
}
print <<<HC_List
<table cellpadding="5" border='1'>
<br>
<font size="6">追加したい艦娘を選んでください</font>
<br><br>
<tr>
	<td></td><!--所属デッキ番号-->
	<td align='center'>艦種</td>
	<td align='center'>Lv</td>
	<td align='center'>艦名</td>
	<td align='center'>耐久</td>
</tr>
HC_List;
while($c_info=mysql_fetch_array($hc_list)){
//	print_r($c_info);print "<br>";
//	print "表示中のcard_id:".$c_info["card_id"]."<br>";
	print	"<tr>";
	print	"<td align='center'>".$c_info["decknum"]."</td>";
	print	"<td align='center'>".$c_info["type"]."</td>";
	print	"<td align='center'>".$c_info["level"]."</td>";
	// 艦娘と艦娘との交換分岐
	if($hc_id==null){		// 交換元に艦娘idがなければ
		print	"<td align='center'>"."<a href='kancolle_deck.php?
					d_num=$d_num&list_num=$list_num&cc_id=$c_info[0]'>".$c_info["name"]."</a>"."</td>";
	}else{					// 交換元に艦娘idがあれば
				print	"<td align='center'>"."<a href='kancolle_deck.php?
					d_num=$d_num&list_num=$list_num&cc_id=$c_info[0]&hc_id=$hc_id'>".$c_info["name"]."</a>"."</td>";
	}
	print	"<td align='center'>".$c_info["hp"]."/".$c_info["maxhp"]."</td>";
	print	"</tr>";
}
print "</table>";
/*--------------------	所持カードの表示(送信リンク付き)	--------------------*/
// 戻るリンク
print "<br><br><a href='kancolle_deck.php'>戻る</a>";

/*********	データベース切断	*********/
mysql_close($s);

?>
</html>