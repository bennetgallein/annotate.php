<?php
namespace Annotations;

/**
 * @AnnotationTarget(value=AnnotatedElementType::ANNOTATION_TYPE)
 */
class AnnotationTarget extends Annotation {
	function value() {
		if ($this->value != null && is_array($this->value)) {
			$bitmask = 0;
			foreach ($this->value as $bit) {
				$bitmask &= $bit;
			}
			$this->value = $bitmask;
		}

		return $this->value;
	}
}
