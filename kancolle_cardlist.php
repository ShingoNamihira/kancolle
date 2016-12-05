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


//************ テンプレ表示
require_once("data/template.php");

/*******	表示内容	*******/
$c=mysql_query("select id,name,type,rare from cards");
?>
<table cellpadding="2" border="1">
<caption>図鑑一覧表示</caption>
<tr>
	<td align="center">図鑑No</td>
	<td align="center">艦名</td>
	<td align="center">艦種</td>
	<td align="center">レア度</td>
</tr>
<?php
while($c_st=mysql_fetch_array($c)){ 
?>
	<tr>
		<td align="center"><?php print $c_st[0] ?></td>
		<td align="center"><?php print $c_st[1] ?></td>
		<td align="center"><?php print $c_st[2] ?></td>
		<td align="center"><?php print $c_st[3] ?></td>
	</tr>

<?php
}
?>
</table>
<?php

/*********	データベース切断	*********/
mysql_close($s);

?>
</html>