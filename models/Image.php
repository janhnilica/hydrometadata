<?php

// poznamky k formatum
// jpeg
//  - nema zadne zvlastni pozadi
// gif
//  - vychozi barva pozadi je cerna, prekreslujeme na pruhlednou
//  - ma jen 256 barev
//  - muze byt animovany (zde neuvazujeme)
// png
//  - vychozi barva pozadi je cerna, prekreslujeme na pruhlednou

class Image
{
	/**
	 * Obrázek typu PNG
	 */
	const IMAGETYPE_PNG = IMAGETYPE_PNG;
	/**
	 * Obrázek typu GIF
	 */
	const IMAGETYPE_GIF = IMAGETYPE_GIF;
	/**
	 * Obrázek typu JPEG
	 */
	const IMAGETYPE_JPEG = IMAGETYPE_JPEG;
	/**
	 * @var resource Obrázek
	 */
	private $image;
	/**
	 * @var int Typ obrázku
	 */
	private $imageType;
	/**
	 * @var int Šířka obrázku v pixelech
	 */
	private $width;
	/**
	 * @var int Výška obrázku v pixelech
	 */
	private $height;

	/**
	 * Inicializuje instanci obrázku
	 * @param string $filename Cesta k souboru (vcetne pripony), ze kterého se má obrázek načíst
	 */
	public function __construct($filename)
	{
		$imageSize = getimagesize($filename);
	   	$this->width = $imageSize[0];
		$this->height = $imageSize[1];
		$this->imageType = $imageSize[2];
		if ($this->imageType == self::IMAGETYPE_JPEG)
                {
			$this->image = imagecreatefromjpeg($filename);
                }
		elseif ($this->imageType == self::IMAGETYPE_GIF)
		{
			// Gify načítáme vždy v true color
			$image = imagecreatefromgif($filename);
			$this->image = $this->createBackground($this->getWidth(), $this->getHeight(), true);
			imagecopy($this->image, $image, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());
		}
		elseif ($this->imageType == self::IMAGETYPE_PNG)
		{
			$this->image = imagecreatefrompng($filename);
			imagealphablending($this->image, true); // Zapnutí alfakanálu
			imagesavealpha($this->image, true); // Ukládání alfakanálu
		}
	}

        // vytvoreni bileho nebo pruhledneho pozadi o danych rozmerech
        // pro obrazky typu gif a png, ktere maji ve vychozim nastaveni cerne pozadi
	private function createBackground($width, $height, $transparent = true)
	{
		$image = imagecreatetruecolor(intval($width), intval($height));
		if ($transparent)
		{
			imagealphablending($image, true);
			$color = imagecolorallocatealpha($image, 0, 0, 0, 127);
		}
		else
                {
			$color = imagecolorallocate($image, 255, 255, 255);
                }
		imagefill($image, 0, 0, $color);
		if ($transparent)
                {
			imagesavealpha($image, true);
                }
		return $image;
	}

	/**
	 * Přidá do pravého dolního rohu obrázku vodoznak
         * vodoznak je obrazek .png, musi byt castecne pruhledny (lze udelat napr. v GIMPu)
	 * @param string $path Cesta k obrázku vodoznaku
	 * @param int $offset Šířka okraje mezi vodoznakem a hranou obrázku v pixelech
	 */
	public function addWatermark($path, $offset = 8)
	{
		$watermark = imagecreatefrompng($path);
		$width = imagesx($watermark);
		$height = imagesy($watermark);
		imagecopy($this->image, $watermark, $this->getWidth() - $width - $offset, $this->getHeight() - $height - $offset, 0, 0, $width, $height);
	}

	/**
	 * Uloží obrázek do souboru
	 * @param string $filename Název souboru
	 * @param int $imageType Typ obrázku
	 * @param int $compression Komprese (pouze pro typ JPEG) v procentech
	 * @param bool $transparent Zda má mít GIF nastavený průhlednou barvu
	 * @param null|int $permissions Nastavení oprávnění pro soubor
	 */
	public function save($filename, $imageType = self::IMAGETYPE_JPEG, $compression = 85, $transparent = true, $permissions = null)
	{
		if ($imageType == self::IMAGETYPE_JPEG)
		{
			$output = $this->createBackground($this->getWidth(), $this->getHeight(), false);
			imagecopy($output, $this->image, 0, 0, 0, 0, intval($this->getWidth()), intval($this->getHeight()));
			imagejpeg($output, $filename, $compression);
		}
		elseif ($imageType == self::IMAGETYPE_GIF)
		{
			$image = $this->createBackground($this->getWidth(), $this->getHeight(), true);
			if ($transparent)
			{
				$color = imagecolorallocatealpha($image, 0, 0, 0, 127);
				imagecolortransparent($image, $color);
			}
			imagecopyresampled($image, $this->image, 0, 0, 0, 0, $this->getWidth(), $this->getHeight(), $this->getWidth(), $this->getHeight());
			imagegif($image, $filename);
		}
		elseif ($imageType == self::IMAGETYPE_PNG)
                {
                    imagepng($this->image, $filename);
                }
                if ($permissions != null)
                {
                    chmod($filename, $permissions);
                }
	}

	/**
	 * Vypíše obrázek na standardní výstup
	 * @param int $imageType Typ obrázku
	 * @param int $compression Komprese (pouze pro typ JPEG) v procentech
	 * @param bool $transparent Zda má mít GIF nastavený průhlednou barvu
	 */
	public function output($imageType = self::IMAGETYPE_JPEG, $compression = 85, $transparent = true)
        {
		$this->save(null, $imageType, $compression, $transparent);
	}

	/**
	 * Vrátí typ obrázku
	 * @return int Typ obrázku
	 */
	public function getImageType()
        {
		return $this->imageType;
	}

	/**
	 * Vrátí šířku obrázku v pixelech
	 * @return int Šířka obrázku v pixelech
	 */
	public function getWidth()
        {
		return $this->width;
	}

	/**
	 * Vrátí výšku obrázku v pixelech
	 * @return int Výška obrázku v pixelech
	 */
	public function getHeight()
        {
		return $this->height;
	}

	/**
	 * Změní velikost obrázku tak, aby se vešel do zadané délky hrany. Poměr stran zůstane zachován.
	 * @param int $edge Délka hrany obrázku v pixelech
	 * @return bool Zda se obrázek změnil nebo již měl požadovanou velikost
	 */
	public function resizeToEdge($edge)
	{
		$width = $this->getWidth();
		$height = $this->getHeight();
		if (($width > $edge) || ($height > $edge))
		{
			if ($width > $height)
                        {
				$this->resizeToWidth($edge);
                        }
			else
                        {
				$this->resizeToHeight($edge);
                        }
			return true;
		}
		return false;
   }
   
	/**
	 * Změní velikost obrázku tak, aby měl minimální hranu o zadané délce. Poměr stran zůstane zachován.
	 * @param int $edge Délka hrany obrázku v pixelech
	 * @return bool Zda se obrázek změnil nebo již měl požadovanou velikost
	 */
	public function resizeToCoverEdge($edge)
	{
		$width = $this->getWidth();
		$height = $this->getHeight();
		if (!($width == $edge && $height >= $edge) || ($height == $edge && $width >= $edge))
		{
			if ($width < $height)
                        {
				$this->resizeToWidth($edge);
                        }
			else
                        {
				$this->resizeToHeight($edge);
                        }
			return true;
		}
		return false;
	}

	/**
	 * Změní velikost obrázku tak, aby měl požadovanou výšku. Poměr stran zůstane zachován.
	 * @param int $height Výška obrázku v pixelech.
	 */
	public function resizeToHeight($height)
	{
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width, $height);
	}

	/**
	 * Změní velikost obrázku tak, aby měl požadovanou šířku. Poměr stran zůstane zachován.
	 * @param int $width Šířka obrázku v pixelech.
	 */
	public function resizeToWidth($width)
	{
		$ratio = $width / $this->getWidth();
		$height = $this->getHeight() * $ratio;
		$this->resize($width, $height);
	}

	/**
	 * Škáluje obrázek v daném poměru
	 * @param int $scale Poměr v procentech (muze byt > 100)
	 */
	public function scale($scale)
	{
		$width = $this->getWidth() * $scale / 100;
		$height = $this->getHeight() * $scale / 100;
		$this->resize($width, $height);
	}

	/**
	 * Ořízne obrázek na danou velikost. Řeže se od levého horního rohu.
	 * @param int $width Šířka obrázku
	 * @param int $height Výška obrázku
	 */
	public function crop($width, $height)
	{
		$image = $this->createBackground($width, $height, true);
		imagecopy($image, $this->image, 0, 0, 0, 0, $width, $height);
		$this->image = $image;
		$this->width = $width;
		$this->height = $height;
   }

	/**
	 * Změní velikost obrázku
	 * @param int $width Požadovaná šířka
	 * @param int $height Požadovaná výška
	 */
	public function resize($width, $height)
	{
		$image = $this->createBackground($width, $height, true);
		imagecopyresampled($image, $this->image, 0, 0, 0, 0, intval($width), intval($height), intval($this->getWidth()), intval($this->getHeight()));
		$this->image = $image;
		$this->width = $width;
		$this->height = $height;
	}

	// Funkce z komentářů z PHP manuálu, dělá to samé jako imagecopymerge, ale podporuje navíc alfakanál
	private function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
	{
		if (!isset($pct))
                {
			return false;
                }
		$pct /= 100;
		// Get image width and height
		$w = imagesx($src_im);
		$h = imagesy($src_im);
		// Turn alpha blending off
		imagealphablending($src_im, false);
		// Find the most opaque pixel in the image (the one with the smallest alpha value)
		$minalpha = 127;
		for ($x = 0; $x < $w; $x++)
                {
			for ($y = 0; $y < $h; $y++)
			{
				$alpha = (imagecolorat($src_im, $x, $y) >> 24) & 0xFF;
				if( $alpha < $minalpha)
                                {
					$minalpha = $alpha;
                                }
			}
                }
		//loop through image pixels and modify alpha for each
		for ($x = 0; $x < $w; $x++)
		{
			for ($y = 0; $y < $h; $y++)
			{
				// get current alpha value (represents the TANSPARENCY!)
				$colorxy = imagecolorat($src_im, $x, $y);
				$alpha = ($colorxy >> 24) & 0xFF;
				// calculate new alpha
				if ($minalpha !== 127)
                                {
					$alpha = 127 + 127 * $pct * ($alpha - 127) / (127 - $minalpha);
                                }
				else
                                {
					$alpha += 127 * $pct;
                                }
				//get the color index with new alpha
				$alphacolorxy = imagecolorallocatealpha($src_im, ($colorxy >> 16) & 0xFF, ($colorxy >> 8) & 0xFF, $colorxy & 0xFF, $alpha);
				//set pixel with the new color + opacity
				if (!imagesetpixel( $src_im, $x, $y, $alphacolorxy))
                                {
					return false;
                                }
			}
		}
		// The image copy
		imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
		return true;
	}

	/**
	 * Přidá pod obrázek odraz
	 * @param int $height Výška odrazu
	 * @param int $startingAlpha Počáteční hodnota průhlednosti
	 */
	public function reflect($height = 16, $startingAlpha = 70)
	{
		// prázdné pozadí
		$background = $this->createBackground($this->getWidth(), $this->getHeight() + $height);
		// přidání obrázku
		imagecopy($background, $this->image, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());
		// přidání odrazu
		$step = $startingAlpha / $height;
		for ($i = 0; $i <= $height; $i++)
		{
			if ($startingAlpha < 0)
                        {
				$startingAlpha = 0;
                        }
			$this->imagecopymerge_alpha($background, $this->image, 0, $this->getHeight() + $i, 0, $this->getHeight() - $i - 1, $this->getWidth(), 1, $startingAlpha);
			$startingAlpha -= $step;
		}
		$this->height = $this->getHeight() + $height;
		$this->image = $background;
	}

	/**
	 * Zjistí zda je daný soubor obrázek
	 * @param string $fileName Cesta k souboru
	 * @return bool Zda je daný soubor obrázek (jpeg, gif, png)
	 */
	public static function isImage($fileName)
	{
		$type = exif_imagetype($fileName);
		return ($type == self::IMAGETYPE_JPEG || $type == self::IMAGETYPE_GIF || $type == self::IMAGETYPE_PNG);
	}
}