<?php
	namespace PM\Image;

	use PM\File\File;
	use WideImage\Image;
	use WideImage\WideImage;

	class GDImageManipulator implements ImageManipulatorInterface {
		/* @var Image $_wi */
		private $_wi;

		/**
		 * @param File|resource|string $file_in
		 * @return ImageManipulatorInterface
		 */
		public static function read($file_in) {
			$self = new self();

			if (is_object($file_in) && $file_in instanceof File)
				$file_in = $file_in->getGDHandle();

			$self->_wi = WideImage::load($file_in);
			return $self;
		}

		public function resize($width,
		                       $height,
		                       \string $fit = self::FIT_INSIDE,
		                       \string $scale = self::SCALE_ANY) : ImageManipulatorInterface {
			$this->_wi = $this->_wi->resize($width, $height, $fit, $scale);
			return $this;
		}

		public function crop($left, $top, $width, $height) : ImageManipulatorInterface {
			$this->_wi = $this->_wi->crop($left, $top, $width, $height);
			return $this;
		}

		public function rotate(\int $angle) : ImageManipulatorInterface {
			$this->_wi = $this->_wi->rotate($angle);
			return $this;
		}

		public function flip(\bool $vertical = false) : ImageManipulatorInterface {
			if ($vertical)
				$this->rotate(90);

			$this->_wi = $this->_wi->flip();

			if ($vertical)
				$this->rotate(-90);

			return $this;
		}

		public function save(\string $path) {
			$this->_wi->saveToFile($path);
		}

		public function getGDResource() {
			return $this->_wi->getHandle();
		}

		public function getStream(\string $format = "png") {
			return fopen("data:text/plain;base64," . base64_encode($this->_wi->asString($format)), "r");
		}

		public function output(\string $format = "png") {
			$this->_wi->output($format);
		}
	}