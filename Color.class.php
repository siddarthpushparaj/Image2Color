<?php

class Color
{
	public $rgb;

	public function __construct($r, $g, $b)
	{
		$this->rgb = array($r, $g, $b);
		return $this;
	}

	public function toXYZ()
	{
		$rgb = array_map(function($e)
		{
			$e = $e / 255;
			if ($e > 0.04045) 
			{
				$e = pow((($e + 0.055) / 1.055), 2.4);
			}
			else
			{
				$e = $e / 12.92;
			}
			return $e * 100;

		}, $this->rgb);

		return array(
			($rgb[0] * 0.4124) + ($rgb[1] * 0.3576) + ($rgb[2] * 0.1805),
			($rgb[0] * 0.2126) + ($rgb[1] * 0.7152) + ($rgb[2] * 0.0722),
			($rgb[0] * 0.0193) + ($rgb[1] * 0.1192) + ($rgb[2] * 0.9505));
	}

	public function toLabCie()
	{
		$xyz = $this->toXYZ();

		$xyz[0] /= 95.047;
		$xyz[1] /= 100;
		$xyz[2] /= 108.883;

		$xyz = array_map(function($e)
		{
			if ($e > 0.008856) 
			{
				return pow($e, 1/3);
			} 
			else 
			{
				return (7.787 * $e) + (16 / 116);
			}

		}, $xyz);

		return array(
			(116 * $xyz[1]) - 16,
			500 * ($xyz[0] - $xyz[1]),
			200 * ($xyz[1] - $xyz[2]));
	}
}