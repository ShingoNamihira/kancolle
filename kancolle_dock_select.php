<?php

/**********	データベース情報などの読み込み	**********/
require_once("data/db_info.php");

/************	データベースへ接続、デーたベース選択	***********/
$s=mysql_connect($SERV,$USER,$PASS) or die("失敗しました");
mysql_select_db($DBNM);

/*************	タイトル、画像などの表示	*************/
print <<<disp1
	<html>
		<head>
			<meta http-equiv="Content-Type"
			 content="text/html;charset=shift_JIS">
			<title>艦これ</title>
		</head>
		<body BGCOLOR="lightsteelblue">
		</body>
disp1;

/*******	表示内容	*******/

$c=mysql_query("select hc.card_num, cards.name, hc.level, hc.hp, hc.maxhp
				from havecards as hc
				join cards on hc.card_id=cards.id
				where hc.player_id=$P_ID and hc.hp<hc.maxhp and hc.state=0");
if(mysql_num_rows($c)>0){
print <<<disp2
<br><br><br>
<table cellpadding="5" border='1'>
<font size="6">入渠する艦娘を選んでください</font>
<br><br>
<tr>
	<td align="center">Lv</td>
	<td align="center">艦名</td>
	<td align="center">耐久</td>
</tr>
disp2;

while($c_st=mysql_fetch_array($c)){ 
	if($c_st["hp"]>=$c_st["maxhp"])continue;
	print	"<tr>";
	print	"<td>".$c_st["level"]."</td>";
	print	"<td>"."<a href='kancolle_dock.php?hc_id=$c_st[0]'>".$c_st["name"]."</a>"."</td>";
	print	"<td>".$c_st["hp"]."/".$c_st["maxhp"]."</td>";
	print	"</tr>";
	flush();	// 表示させやすくするため
}
print <<<BACK
</table>
<br><br>
<a href="kancolle_dock.php">前に戻る</a>
BACK;
}else{
print <<<ZERO
<br><br><br><br>
傷ついた艦娘はいないようです(*´ω｀*)
<br><br><br>
<a href="kancolle_dock.php">戻る</a>
ZERO;
}


/*********	データベース切断	*********/
mysql_close($s);

?>
</html>