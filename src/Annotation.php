<?php
namespace Annotations;

abstract class Annotation {
	public $value;

	function __construct($value = null) {
		$this->value = $value;
	}

	function value() {
		return $this->value;
	}
}