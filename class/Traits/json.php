<?php
	namespace PM\Traits;

	use PM\Utility;

	trait JSON {
		public function toArray() : array {
			$arr = get_object_vars($this);

			foreach ($arr as $key => $value)
				if (Utility::charAt($key, 0) == '_')
					unset($arr[$key]);

			return $arr;
		}
	}