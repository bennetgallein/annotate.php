<?php
namespace Annotations;

class AnnotatedReflectionMethod extends \ReflectionMethod implements AnnotatedElement {

	function getAnnotatedElementType() {
		return (AnnotatedElementType::METHOD | ($this->isConstructor() ? AnnotatedElementType::CONSTRUCTOR : 0));
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