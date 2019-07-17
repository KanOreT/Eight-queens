<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>8 Ферзів</title>
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>

<?php
$db_connect=mysqli_connect('localhost','root','root','mydb'); // connect
$Qq=(mysqli_fetch_assoc(mysqli_query($db_connect,"SELECT * FROM `counter`")))["queen quantity"];// Qq - к-сть ферзів

$q=0; // потрібно для лінійного масиву з усіх ігрових клітинок
fill_lin_chess_table();
for ($i=1; $i <= $Qq; $i++) $coord[$i]=(mysqli_fetch_assoc(mysqli_query($db_connect,"SELECT * FROM `queens` where numer = $i")))["coords"];



if (isset($_POST['Hint']))   //підказка
switch (!$Qq)
{
	case 1:
		echo "Можете поставити наступного ферзя будь-де";
		break;
	case 0:
		if($Qq==8) break;
		$querry[0]="SELECT * FROM  tip where ".$coord[1]." = 1";
		if($Qq>1) for ($i=2; $i <= $Qq ; $i++) $querry[0].=" && ".$coord[$i]." = 1";
		$sol_quantity=0;
		for ($i=1; $i <=92 ; $i++) //шукаєм усі розв'язки
		{ 
			$querry[$i]=$querry[0]." && id =".$i;
			if(mysqli_fetch_assoc(mysqli_query($db_connect,$querry[$i])))
			{
				++$sol_quantity;
				$solution[$sol_quantity]=mysqli_fetch_assoc(mysqli_query($db_connect,$querry[$i]));
			};
		}
		if($sol_quantity)
		{
			 $sol_numb=rand(1,$sol_quantity);	
			 $cell_quantity=0;
			 for ($i=1; $i <=64 ; $i++)  if(($solution[$sol_numb][$lin_chess_table[$i]])&&(!is_q_put_by_user($lin_chess_table[$i])))
			{
				++$cell_quantity;
				$tip_vars[$cell_quantity]=$lin_chess_table[$i];
			}
			echo "Розв'язкiв:".$sol_quantity.". Можете поставити наступного ферзя на ".$tip_vars[rand(1,$cell_quantity)];
		}			
		else echo "Ви вже не зможете розставити усі 8 ферзів";
}



if((array_key_exists('queen',$_GET))&&(check_coord_user($_GET['queen'])))             //юзер ввів
{
	$coords=$_GET['queen'];
	$xq=(caps($coords[0])) ? (ord($coords[0])-96):(ord($coords[0])-64);
	$yq=(ord($coords[1])-48);
	if(!be_cell_beaten($xq,$yq)) //додаєм ферзя
	{
		if (!caps($coords[0])) $coords=(chr($xq+96)).(chr($yq+48));
		$Qq++;
		$result=mysqli_query($db_connect,"INSERT INTO queens VALUES('$Qq', '$coords','$xq','$yq')");
		$result=mysqli_query($db_connect,"UPDATE counter set `queen quantity`='$Qq'");
		$coord[$Qq]=$coords;
	}
	elseif(position($xq,$yq)) //забираєм ферзя
	{
		$row=mysqli_fetch_assoc(mysqli_query($db_connect,"SELECT * FROM `queens` where coords = '$coords' "));
		for ($i=$row["numer"]+1; $i <= $Qq ; $i++)
		{
			$k=$i-1;
			$a=$coord[$i];
			$xq=(caps($coord[$i][0])) ? (ord($coord[$i][0])-96):(ord($coord[$i][0])-64);
			$yq=(ord($coord[$i][1])-48);
			$result=mysqli_query($db_connect,"UPDATE `queens` SET `coords`='$a', `absc`='$xq', oord='$yq'  WHERE `numer`='$k'");
		}
		$result=mysqli_query($db_connect,"DELETE FROM `queens` WHERE `numer`='$Qq'");
		$Qq--;
		if(!($Qq==-1))$result=mysqli_query($db_connect,"UPDATE `counter` SET `queen quantity`='$Qq'");
	}
}


if (isset($_POST['Restart_butt']))        // рестарт
{	
	$result=mysqli_query($db_connect, "UPDATE counter set `queen quantity`='0';");
	$result=mysqli_query($db_connect, "TRUNCATE TABLE queens");
	$Qq=0;
}

if (array_key_exists('queen',$_GET)||(isset($_POST['Restart_butt']))) header("Location: Eight_queens_bd.php");

if ($Qq==8) echo "Ви молодець!";




$un_reds=64;
//Заповняєм таблицю
echo'<table><tr><td class="white np"></td>'; //top left 
for($k=1;$k<9;$k++)  echo'<td class="white np">'.chr($k+64).'</td>';        //     А - Н  (top)
echo'<td class="white np"></td></tr>'; //top right
for($i=8;$i>0;$i--)
{
	echo'<tr><td class="white np">'.$i.'</td>';  //   8 - 1 (left)
	for($j=1;$j<9;$j++)
	{
		echo '<td onclick="window.location = '."'Eight_queens_bd.php?queen=".(chr(96+$j).chr(48+$i))."'".'" class="';//
		if (be_cell_beaten($j,$i))
		{
			echo'red ';
			--$un_reds;
		}
		if (position($j,$i)) echo '">Q'.$Qnumb.'</td>';
		else
		{
			if(($i+$j)%2==0)echo'black';
			else echo'white';
			echo '"></td>';
		}
	}
	echo '<td class="white np">'.$i.'</td></tr>';  // 8 - 1 (right)
}
echo '<tr><td class="white np"></td>';  //bot left
for ($k=1; $k < 9; $k++) echo '<td class="white np">'.chr($k+64).'</td>';  // А - Н (bot)
echo '<td class = "white np"></td></tr></table>'; //bot right
if(!($un_reds)&&(!($Qq==8))) echo "Невдача!";





function does_queen_beat($x,$y,$xq,$yq) //чи конкретний ферзь б'є
{
	return (($x==$xq)or($y==$yq)or(abs($x-$xq)==abs($y-$yq)));
}
function check_coord_user($s) // перевірка чи юзер правильно ввів
{
	if( (strlen($s)==2)
	&&  (abs(ord($s[1])-52.5)<5) )
	{
		if( (abs(ord($s[0])-99.5)<5)
		||  (abs(ord($s[0])-68.5)<5) )
		{
			return true;
		}
	}
}
function be_cell_beaten($x,$y)  // чи будь-який ферзь б'є
{
	global $Qq, $db_connect;
	if ($Qq==0) return false;
	for ($i=1; $i <= $Qq ; ++$i)
	{
		$row=mysqli_fetch_assoc(mysqli_query($db_connect,"SELECT * FROM `queens` where numer = '$i' "));
		$xq=$row["absc"];
		$yq=$row["oord"];
		if (does_queen_beat($x,$y,$xq,$yq)) return true;
	}
	return false;
}
function position($x,$y)  // чи на клітинці ферзь
{
	global $Qq, $db_connect,$Qnumb;
	for ($i=1; $i <= $Qq ; ++$i)
	{
		$row=mysqli_fetch_assoc(mysqli_query($db_connect,"SELECT * FROM `queens` where numer = '$i' "));
		$xq=$row["absc"];
		$yq=$row["oord"];
		$Qnumb=$i;
		if (($x==$xq) && ($y==$yq)) return true;
	}
	return false;
}
function caps($s) // чи текст написаний капсом
{
	return (ord($s)>=90); //вистачає 72
}
function fill_lin_chess_table() //заповняєм лін. масив усіма координатами
{
	global $q, $lin_chess_table;
	for ($i=1; $i <= 8 ; $i++)
	{
		for ($j=1; $j <= 8 ; $j++)
		{
			++$q;
			$lin_chess_table[$q]=(chr($i+96)).(chr($j+48));
		}
	}
}
function is_q_put_by_user($a) //чи ферзь вже є в таблиці
{
	global $Qq, $coord;
	for ($i=1; $i <=$Qq ; $i++) if ($a==$coord[$i]) return true;
	return false;
}
?>
<form method="get">
	<p>Поставити ферзя/забрати ферзя з клітинки: <input type="text" name="queen" autofocus="" /></p>
</form>
<form method="post">
	<button name="Restart_butt">Почати заново</button>
	<button name="Hint">Отримати підказку</button>
</form>
<a href="Eight_queens_bd_table">Подивитись усі розв'язки</a>
</body>
</html>