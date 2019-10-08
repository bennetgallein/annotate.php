<?php
namespace Annotations;

class AnnotatedReflectionProperty extends ReflectionProperty implements AnnotatedElement {

	function getAnnotatedElementType() {
		return AnnotatedElementType::PROPERTY;
	}

	private $annotations;

	function getAnnotations() {
		if ($this->annotations == null) {
			$this->annotations = AnnotationParser::parse($this);
		}
		return $this->annotations;
	}

	function getAnnotation($annotationClass) {
		$all = $this->getAnnotations();
		return (isset($all[$annotationClass]) ? $all[$annotationClass] : null);
	}

	function hasAnnotation($annotationClass) {
		return $this->getAnnotation($annotationClass) != null;
	}

}