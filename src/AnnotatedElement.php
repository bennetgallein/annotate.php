<?php
namespace Annotations;

interface AnnotatedElement {
	function getAnnotatedElementType();

	function getAnnotations();

	function getAnnotation($annotationClass);

	function hasAnnotation($annotationClass);

	function getDocComment();
}