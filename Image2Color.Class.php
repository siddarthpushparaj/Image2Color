<?php
include(__DIR__.'/colorsets.php');
include(__DIR__.'/Color.class.php');


class Image2Color
{
	// options
	private $maxsurface = 1000;

	// properties
	public $colorset 	= null;
	private $im 		= null;
	private $owidth 	= null;
	private $oheight 	= null;
	private $nwidth 	= null;
	private $nheight 	= null;
	private $colorcount = null;

	public function __construct($colorset)
	{
		$colorset = array_map(function($e)
		{
			$c = new Color($e['rgb'][0], $e['rgb'][1], $e['rgb'][2]);
			$e['lab'] = $c->toLabCie();

			return $e;

		}, $colorset);
		$this->colorset = $colorset;
		$this->reset();
	}

	public function reset()
	{
		$this->colorcount = array_map(function($n){ return array('label' => $n['label'], 'count' => 0);}, $this->colorset);
	}

	public function analyse($filename, $type=null)
	{
		switch(is_null($type) ? pathinfo($filename)['extension'] : $type)
		{
			case 'jpg':
			case 'jpeg':
				$this->im = imagecreatefromjpeg($filename);
			break;
			case 'png':
				$this->im = imagecreatefrompng($filename);
			break;
			case 'gif':
				$this->im = imagecreatefromgif($filename);
			default:
				exit;
			break;
		}

		// Redimensionnement de l'image
		$this->owidth = imagesx($this->im);
		$this->oheight = imagesy($this->im);
		$surface = $this->owidth*$this->oheight;

		$ratio = sqrt($this->maxsurface/$surface);
		$this->nwidth = round($this->owidth*$ratio);
		$this->nheight =  round($this->oheight*$ratio);

		$im = imagecreatetruecolor($this->nwidth, $this->nheight);
		imagecopyresized($im, $this->im, 0, 0, 0, 0, $this->nwidth, $this->nheight, $this->owidth, $this->oheight);

		$this->im = $im;
		$rtn = $this->roaming();
		uasort($rtn, function($a, $b){ return ($a['count'] > $b['count']) ? -1 : 1;});
		$this->reset();
		return $rtn;

	}

	private function roaming()
	{

		for($x = 0; $x < $this->nwidth; $x++)
		{
			for($y = 0; $y < $this->nheight; $y++)
			{
				$rgb = imagecolorat($this->im, $x, $y);

				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;

				$index = $this->compareTo($r, $g, $b);
				$this->colorcount[$index]['count']++;
			}
		}
		return $this->colorcount;
	}

	public function compareTo($r, $g, $b)
	{
		$colordelta = array();
		$ct = new Color($r, $g, $b);
		$ct = $ct->toLabCie();

		$matchDist = 10000;
		$matchKey = null;

		foreach ($this->colorset as $key => $color) 
		{
			$dist = $this->getDistanceLabCie($ct, $color['lab']);

			if ($dist < $matchDist)
			{
				$matchDist = $dist;
				$matchKey = $key;
			}
		}

		return $matchKey;
	}

	public function getDistanceLabCie($lab1, $lab2)
	{
		$lDiff = abs($lab2[0] - $lab1[0]);
		$aDiff = abs($lab2[1] - $lab1[1]);
		$bDiff = abs($lab2[2] - $lab1[2]);
		
		return sqrt($lDiff + $aDiff + $bDiff);
	}

	public function getImage()
	{
		ob_start();
			imagejpeg($this->im);
			$contents = ob_get_contents();
		ob_end_clean();

		return "data:image/jpeg;base64," . base64_encode($contents);
	}
}