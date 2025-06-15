<?php
/**
 * File Streamer Class.
 *
 * @package suremembers
 * @since 1.3.0
 */

namespace SureMembers\Inc;

/**
 * Streamer class to handle display of files.
 *
 * @since 1.3.0
 */
class Streamer {
	/**
	 * File Path.
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 * File Stream
	 *
	 * @var string
	 */
	private $stream = '';

	/**
	 * Buffer.
	 *
	 * @var integer
	 */
	private $buffer = 0;

	/**
	 * Start.
	 *
	 * @var integer
	 */
	private $start = -1;

	/**
	 * End.
	 *
	 * @var integer
	 */
	private $end = -1;

	/**
	 * Size.
	 *
	 * @var integer
	 */
	private $size = 0;

	/**
	 * Type.
	 *
	 * @var string
	 */
	private $type = '';

	/**
	 * Streamer constructor.
	 *
	 * @param string  $file_path File path.
	 * @param string  $file_type File type.
	 * @param integer $buffer Buffer value.
	 */
	public function __construct( $file_path, $file_type, $buffer = 102400 ) {
		$this->path   = $file_path;
		$this->type   = $file_type;
		$this->buffer = $buffer;
	}

	/**
	 * Start streaming video content
	 *
	 * @return void
	 */
	public function start() {
		$this->open();
		$this->set_header();
		$this->stream();
		$this->end();
	}

	/**
	 * Open stream
	 *
	 * @return void
	 */
	private function open() {

		$this->stream = fopen( $this->path, 'rb' ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen

		if ( ! $this->stream ) {
			die( 'Could not open stream for reading' );
		}
	}

	/**
	 * Set proper header to serve the video content
	 *
	 * @return void
	 */
	private function set_header() {

		$this->start            = 0;
		$this->size             = filesize( $this->path );
		$this->end              = $this->size - 1;
		$file_modification_time = ! empty( $this->path ) ? intval( @filemtime( $this->path ) ) : time(); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		ob_get_clean();
		header( 'Content-Type: ' . $this->type );
		header( 'Cache-Control: max-age=2592000, public' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 2592000 ) . ' GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $file_modification_time ) . ' GMT' );
		header( 'Accept-Ranges: 0-' . $this->end );
		header( 'Content-Disposition: inline; filename="' . basename( $this->path ) . '"' );

		if ( isset( $_SERVER['HTTP_RANGE'] ) ) {

			$c_start = $this->start;
			$c_end   = $this->end;

			// Ignoring sanitization in favor of functionality.
			list( , $range ) = explode( '=', $_SERVER['HTTP_RANGE'], 2 ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( strpos( $range, ',' ) !== false ) {
				header( 'HTTP/1.1 416 Requested Range Not Satisfiable' );
				header( "Content-Range: bytes $this->start-$this->end/$this->size" );
				exit;
			}
			if ( '-' === $range ) {
				$c_start = $this->size - substr( $range, 1 );
			} else {
				$range   = explode( '-', $range );
				$c_start = $range[0];
				$c_end   = ( isset( $range[1] ) && is_numeric( $range[1] ) ) ? $range[1] : $c_end;
			}
			$c_end = ( $c_end > $this->end ) ? $this->end : $c_end;
			if ( $c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size ) {
				header( 'HTTP/1.1 416 Requested Range Not Satisfiable' );
				header( "Content-Range: bytes $this->start-$this->end/$this->size" );
				exit;
			}
			$this->start = $c_start;
			$this->end   = $c_end;
			$length      = $this->end - $this->start + 1;
			if ( ! is_string( $this->stream ) ) {
				fseek( $this->stream, $this->start );
			}
			header( 'HTTP/1.1 206 Partial Content' );
			header( 'Content-Length: ' . $length );
			header( "Content-Range: bytes $this->start-$this->end/" . $this->size );
		} else {
			header( 'Content-Length: ' . $this->size );
		}
	}

	/**
	 * Perform the streaming of calculated range.
	 *
	 * @return void
	 */
	private function stream() {

		$i = $this->start;
		set_time_limit( 0 );

		if ( ! is_string( $this->stream ) ) {
			while ( ! feof( $this->stream ) && $i <= $this->end ) {
				$bytes_to_read = $this->buffer;
				if ( ( $i + $bytes_to_read ) > $this->end ) {
					$bytes_to_read = $this->end - $i + 1;
				}

				if ( $bytes_to_read > 0 ) {
					$data = fread( $this->stream, $bytes_to_read ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fread
					// @codingStandardsIgnoreLine
					echo $data;
					flush();
					$i += $bytes_to_read;
				}
			}
		}
	}

	/**
	 * Close currently opened stream.
	 *
	 * @return void
	 */
	private function end() {
		if ( ! is_string( $this->stream ) ) {
			fclose( $this->stream ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		}
		exit;
	}
}
