<?php

namespace Annotations;

class AnnotationParser {
	// Tags used for generating PHP Docs (http://www.phpdoc.org/)
	private static $TAGS = array(
		'@abstract', '@access', '@author',
		'@copyright', '@deprecated', '@deprec', '@example', '@exception',
		'@global', '@ignore', '@internal', '@param', '@return', '@link',
		'@name', '@magic', '@package', '@see', '@since', '@static',
		'@staticvar', '@subpackage', '@throws', '@todo', '@var', '@version'
	);

	/**
	 * Parses an element's DocComment for Annotations
	 *
	 * @param AnnotatedElement $element Element to parse DocComment for
	 * @return array
	 * @throws AnnotationParseException
	 */
	static function parse(AnnotatedElement $element) {
		$annotations = array();
		$comment = $element->getDocComment();

		// Parsing is only required if comment is present
		if (strlen(trim($comment)) > 0) {
			$matches = array();

			// Find all annotations (this may include PHP Doc tags)
			preg_match_all('/@(.*)[\n|\*]/', $comment, $matches);
			foreach ($matches[1] as $match) {
				$match = trim($match);
				$args = [];
				$props = [];

				// Annotation with parameters
				if (strpos($match, '(') > 0) {
					$parts = array();
					preg_match_all('/^(.*?)\((.*?)\)/', $match, $parts);
					$name = $parts[1][0];

					// Don't process any further if annotation is a PHP Doc tag
					if (in_array('@' . $name, self::$TAGS)) continue;

					// Break parts up into individual args/props
					$t = array();
					$tmp = '';
					$arr = false;
					for ($i = strlen($parts[2][0]) - 1; $i >= 0; $i--) {
						$chr = $parts[2][0][$i];
						if ($chr == '}') {
							$arr = true;
						} else if ($chr == '{') {
							$arr = false;
						} else if ($chr == ',' && !$arr) {
							$t[] = strrev($tmp);
							$tmp = '';
						} else {
							$tmp .= $chr;
						}
					}
					$t[] = strrev($tmp);
					$t = array_reverse($t);

					// Assign args/props accordingly
					foreach ($t as $a) {
						if (strlen(trim($a)) == 0) continue;

						// Named properties
						if (strpos($a, '=') > 0) {
							$kv = explode('=', $a);
							$props[trim($kv[0])] = self::value($kv[1]);
						}
						// Constructor arguments
						else {
							$args[] = self::value($a);
						}
					}

					// Don't allow mixing args and props
					if (sizeof($args) > 0 && sizeof($props) > 0) {
						throw new AnnotationParseException('Annotation "' . $name . '" cannot use both named properties and constructor arguments');
					}
				}
				// No parameters
				else {
					$name = trim($match);

					// Don't process any further if annotation is a PHP Doc tag
					if (in_array('@' . $name, self::$TAGS)) continue;
				}

				$result = self::create($element, $name, $args, $props);
				if ($result != null) {
					$annotations[$name] = $result;
				}
			}
		}

		return $annotations;
	}

	/**
	 * Evaluates the value of an argument or property
	 *
	 * @param string $val Value to be evaluated
	 * @return mixed
	 */
	private static function value($val) {
		$val = trim($val);

		// array
		if (strpos($val, ',') > 0) {
			$val = explode(',', $val);
			foreach ($val as $idx => $tmp) {
				$val[$idx] = self::value($tmp);
			}
		}
		// string
		else if (preg_match('/^([\'"]).*([\'"])$/', $val)) {
			$val = substr($val, 1);
			$val = substr($val, 0, strlen($val) - 1);
		}

		return $val;
	}

	/**
	 * Creates an instance of the specified Annotation
	 *
	 * @param AnnotatedElement $element Element that Annotation was declared for
	 * @param string $name Name of the Annotation class
	 * @param array $args Arguments to pass to the Annotation constructor
	 * @param array $props Properties to populate the Annotation with
	 * @return Annotation
	 * @throws AnnotationParseException
	 */
	private static function create(AnnotatedElement $element, $name, $args = null, $props = null) {
		$result = null;

		// Ensure that the class exists
		if (class_exists($name)) {
			$class = new AnnotatedReflectionClass($name);

			// Ensure that class is a subclass of Annotation
			if ($class->isSubClassOf('Annotations\Annotation')) {
				// Validate annotation target
				self::validate($element, $class);

				// Instantiate annotation with constructor arguments
				$result = $class->newInstanceArgs($args == null ? array() : $args);

				// Populate annotation properties
				if (is_array($props) && sizeof($props) > 0) {
					foreach ($props as $key => $val) {
						if ($class->getProperty($key) == null) {
							throw new AnnotationParseException('Invalid property ' . $key . ' for Annotation ' . $name);
						}
						$result->$key = $val;
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Validates that an element is an acceptable target for an Annotation
	 * 
	 * @param AnnotatedElement $element
	 * @param AnnotatedReflectionClass $class
	 * @return void
	 * @throws AnnotationTargetException
	 */
	private static function validate(AnnotatedElement $element, AnnotatedReflectionClass $class) {
		if ($element->getName() == 'Annotations\AnnotationTarget') return;

		if ($class->hasAnnotation('Annotations\AnnotationTarget')) {
			$target = $class->getAnnotation('Annotations\AnnotationTarget');
			if (
				$target->value() != null && $target->value() > 0 &&
				!(($target->value() & (float) $element->getAnnotatedElementType()) == $target->value())
			) {
				throw new AnnotationTargetException('Invalid annotation "' . $class->getName() . '" for "' . $element->getName() . '"');
			}
		}
	}
}
