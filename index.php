<!doctype html>
<html lang="fr">
<head>
	<title>Ikolor Lib</title>
	<meta charset="utf-8" />
	<style>
		body > div
		{
			display: inline-block;
			width: 300px;
			height: 150px;
		}

		body > div > img
		{
			float:left;
			margin: 0;
			max-width: 150px;
			max-height: 150px;
		}

		body > div > img:nth-child(2)
		{
			display: none;
			margin-top: 150px;
		}

		body > div > div
		{
			float:right;
			width: 150px;
			height: 150px;
		}

		body > div > div > ul
		{
			text-decoration: none;
			list-style-type: none;
		}

		body > div > div > ul > li
		{
			display: inline-block;
			width: 20px;
			height: 20px;
			margin: 0;
			padding: 0;

		}
	</style>
</head>
<body>
<?php

include('./Image2Color.class.php');
$path = ('./img/');

$ik = new Image2Color($colorsets[0]);

if ($handle = opendir($path)) 
{
	while (false !== ($file = readdir($handle))) 
	{
		if($file != '.' && $file != '..')
		{?>
				<div>
					<img src="./img/<?=$file?>" />
					<div>
						<ul>
							<?php foreach ($ik->analyse('./img/'.$file) as $key => $color): ?>
							<li style="background-color:rgb(<?=implode(',', $ik->colorset[$key]['rgb'])?>)"></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<img src="<?=$ik->getImage()?>" />
				</div>
<?php
		}
	}
	closedir($handle);
}

?>
</body>
</html>