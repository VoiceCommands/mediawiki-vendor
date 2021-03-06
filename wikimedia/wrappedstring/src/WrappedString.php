<?php
/**
 * Copyright (c) 2015 Timo Tijhof <krinklemail@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @file
 */

namespace Wikimedia;

class WrappedString {
	/** @var string */
	protected $value;

	/** @var string */
	protected $prefix;

	/** @var string */
	protected $suffix;

	/**
	 * @param string $value
	 * @param string $prefix
	 * @param string $suffix
	 */
	public function __construct( $value, $prefix = null, $suffix = null ) {
		$this->value = $value;
		$this->prefix = $prefix;
		$this->suffix = $suffix;
	}

	/**
	 * @param string $value
	 * @return WrappedString Newly wrapped string
	 */
	protected function extend( $value ) {
		$wrap = clone $this;
		$suffixlen = strlen( $this->suffix );
		if ( $suffixlen ) {
			$wrap->value = substr( $this->value, 0, -$suffixlen );
		}
		$wrap->value .= substr( $value, strlen( $this->prefix ) );
		return $wrap;
	}

	/**
	 * Merge consecutive WrappedString objects with the same prefix and suffix.
	 *
	 * Does not modify the array or the WrappedString objects.
	 *
	 * NOTE: This is an internal method. Use join() or WrappedStringList instead.
	 *
	 * @param array $wraps
	 * @return string[] Compacted list to be treated as strings
	 * (may contain WrappedString and WrappedStringList objects)
	 */
	public static function compact( array $wraps ) {
		$consolidated = [];
		$prev = current( $wraps );
		while ( next( $wraps ) !== false ) {
			$wrap = current( $wraps );
			if ( $prev instanceof WrappedString
				&& $wrap instanceof WrappedString
				&& $prev->prefix !== null
				&& $prev->prefix === $wrap->prefix
				&& $prev->suffix !== null
				&& $prev->suffix === $wrap->suffix
			) {
				$prev = $prev->extend( $wrap->value );
			} else {
				$consolidated[] = $prev;
				$prev = $wrap;
			}
		}
		// Add last one
		$consolidated[] = $prev;

		return $consolidated;
	}

	/**
	 * Join several wrapped strings with a separator between each.
	 *
	 * This method is compatibile with native PHP implode(). The actual join
	 * operation is deferred to WrappedStringList::__toString(). This allows
	 * callers to collect multiple lists and compact them together.
	 *
	 * @param string $sep
	 * @param WrappedString[] $wraps
	 * @return WrappedStringList
	 */
	public static function join( $sep, array $wraps ) {
		return new WrappedStringList( $sep, $wraps );
	}

	/** @return string */
	public function __toString() {
		return $this->value;
	}
}
