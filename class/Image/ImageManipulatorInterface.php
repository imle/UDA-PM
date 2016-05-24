<?php
	namespace PM\Image;

	use League\Flysystem\FilesystemInterface;
	use PM\File\File;

	interface ImageManipulatorInterface {
		const SC_RIGHT = "right";
		const SC_BOTTOM = "bottom";
		const SC_CENTER = "center";
		const SC_MIDDLE = "middle";
		const FIT_INSIDE = "inside";
		const FIT_OUTSIDE = "outside";
		const FIT_FILL = "fill";
		const SCALE_DOWN = "down";
		const SCALE_UP = "up";
		const SCALE_ANY = "any";

		/**
		 * @param File $file_in
		 * @param FilesystemInterface $_fs
		 * @return ImageManipulatorInterface
		 */
		public static function read(File $file_in, FilesystemInterface $_fs);

		/**
		 * @param mixed $width -- The new width (smart coordinate), or null.
		 * @param mixed $height -- The new height (smart coordinate), or null.
		 * @param string $fit -- 'inside', 'outside', 'fill'
		 * @param string $scale -- 'down', 'up', 'any'
		 * @return ImageManipulatorInterface
		 */
		public function resize($width,
		                       $height,
		                       \string $fit = self::FIT_INSIDE,
		                       \string $scale = self::SCALE_ANY) : self;

		/**
		 * @param mixed $left   -- Left-coordinate of the crop rect, smart coordinate
		 * @param mixed $top    -- Top-coordinate of the crop rect, smart coordinate
		 * @param mixed $width  -- Width of the crop rect, smart coordinate
		 * @param mixed $height -- Height of the crop rect, smart coordinate
		 * @return ImageManipulatorInterface
		 */
		public function crop($left, $top, $width, $height) : self;

		/**
		 * @param int $angle -- Angle in degrees, clock-wise
		 * @return ImageManipulatorInterface
		 */
		public function rotate(\int $angle) : self;

		/**
		 * @param bool $vertical -- Default is horizontal
		 * @return ImageManipulatorInterface
		 */
		public function flip(\bool $vertical = false) : self;

		/**
		 * @param string $path
		 */
		public function save(\string $path);

		/**
		 * @return resource
		 */
		public function getGDResource();

		/**
		 * @param string $format
		 * @return resource
		 */
		public function getStream(\string $format = "png");

		/**
		 * @param string $format
		 */
		public function output(\string $format = "png");
	}