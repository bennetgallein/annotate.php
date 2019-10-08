<?php
namespace Annotations;


class AnnotatedReflectionClass extends ReflectionClass implements AnnotatedElement {

	function getAnnotatedElementType() {
		return (AnnotatedElementType::TYPE | ($this->isSubClassOf('Annotation') ? AnnotatedElementType::ANNOTATION_TYPE : 0));
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

	private $methods;

	function getMethods() {
		if ($this->methods == null) {
			$methods = parent::getMethods();
			$this->methods = array();
			foreach ($methods as $method) {
				$this->methods[$method->getName()] = new AnnotatedReflectionMethod($this->getName(), $method->getName());
			}
		}
		return $this->methods;
	}

	function getMethod($name) {
		$all = $this->getMethods();
		return (isset($all[$name]) ? $all[$name] : null);
	}

	function hasMethod($name) {
		return $this->getMethod($name) != null;
	}

	private $properties;

	function getProperties() {
		if ($this->properties == null) {
			$properties = parent::getProperties();
			$this->properties = array();
			foreach ($properties as $property) {
				$this->properties[$property->getName()] = new AnnotatedReflectionProperty($this->getName(), $property->getName());
			}
		}
		return $this->properties;
	}

	function getProperty($name) {
		$all = $this->getProperties();
		return (isset($all[$name]) ? $all[$name] : null);
	}

	function hasProperty($name) {
		return $this->getProperty($name) != null;
	}

}