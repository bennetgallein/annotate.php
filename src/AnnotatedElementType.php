<?php
namespace Annotations;

interface AnnotatedElementType {
	/**
	 * Annotation type declaration
	 */
	const ANNOTATION_TYPE = 1;

	/**
	 * Constructor declaration
	 */
	const CONSTRUCTOR = 2;

	/**
	 * Method declaration
	 */
	const METHOD = 4;

	/**
	 * Property declaration (includes constants)
	 */
	const PROPERTY = 8;

	/**
	 * Class (including annotation type), or interface declaration
	 */
	const TYPE = 16;
}