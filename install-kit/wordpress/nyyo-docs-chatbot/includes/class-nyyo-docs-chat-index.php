<?php
/**
 * Local index and keyword search (BM25) over the site's own public content.
 *
 * @package NyyoDocsChatbot
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds and queries the local index. Nothing leaves the site.
 */
class Nyyo_Docs_Chat_Index {

	/**
	 * Words ignored by the search.
	 *
	 * @var string[]
	 */
	private static $stop = array( 'a', 'an', 'the', 'is', 'are', 'was', 'were', 'be', 'been', 'do', 'does', 'did', 'how', 'what', 'when', 'where', 'why', 'which', 'who', 'your', 'you', 'my', 'our', 'i', 'me', 'we', 'they', 'it', 'its', 'of', 'in', 'on', 'at', 'to', 'for', 'from', 'with', 'by', 'about', 'and', 'or', 'but', 'if', 'so', 'as', 'this', 'that', 'can', 'could', 'would', 'should', 'will', 'there', 'has', 'have', 'had', 'not', 'no', 'yes', 'please', 'tell' );

	/**
	 * Get the stored index, or null.
	 *
	 * @return array|null
	 */
	public static function get() {
		$index = get_option( NYYO_DOCS_CHAT_INDEX_OPT );
		return is_array( $index ) && isset( $index['chunks'] ) ? $index : null;
	}

	/**
	 * Tokenize text into stemmed, lower-case words.
	 *
	 * @param string $text Text.
	 * @return string[]
	 */
	public static function tokens( $text ) {
		$text = function_exists( 'mb_strtolower' ) ? mb_strtolower( $text, 'UTF-8' ) : strtolower( $text );
		preg_match_all( '/[\p{L}\p{N}]+/u', $text, $m );
		$out = array();
		foreach ( $m[0] as $t ) {
			if ( strlen( $t ) < 2 || in_array( $t, self::$stop, true ) ) {
				continue;
			}
			$out[] = self::stem( $t );
		}
		return $out;
	}

	/**
	 * Very small English suffix stemmer.
	 *
	 * @param string $t Token.
	 * @return string
	 */
	private static function stem( $t ) {
		if ( strlen( $t ) <= 3 || ctype_digit( $t ) ) {
			return $t;
		}
		foreach ( array( 'ies' => 'y', 'ing' => '', 'ed' => '', 'es' => '', 's' => '' ) as $suf => $rep ) {
			$l = strlen( $suf );
			if ( substr( $t, -$l ) === $suf && strlen( $t ) - $l >= 3 && substr( $t, -2 ) !== 'ss' ) {
				return substr( $t, 0, -$l ) . $rep;
			}
		}
		return $t;
	}

	/**
	 * Build the index from published, public, non password-protected content.
	 *
	 * @param array $settings Plugin settings.
	 * @return array
	 */
	public static function build( $settings ) {
		$query  = new WP_Query(
			array(
				'post_type'           => (array) $settings['post_types'],
				'post_status'         => 'publish',
				'has_password'        => false,
				'posts_per_page'      => NYYO_DOCS_CHAT_MAX_POSTS,
				'orderby'             => 'modified',
				'order'               => 'DESC',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);
		$chunks = array();
		$df     = array();
		$total  = 0;
		foreach ( $query->posts as $post ) {
			$html = preg_replace( '#<br\s*/?>|</(p|div|li|h[1-6]|tr|td|th|blockquote)>#i', ' ', strip_shortcodes( (string) $post->post_content ) );
			$text = wp_strip_all_tags( (string) $html, true );
			$text = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( $text, ENT_QUOTES, 'UTF-8' ) ) );
			if ( '' === $text ) {
				continue;
			}
			$title = html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, 'UTF-8' );
			$url   = get_permalink( $post );
			$words = explode( ' ', $text );
			$size  = NYYO_DOCS_CHAT_CHUNK_WORDS;
			for ( $i = 0, $n = count( $words ); $i < $n; $i += $size - 20 ) {
				$piece = implode( ' ', array_slice( $words, $i, $size ) );
				$tf    = array_count_values( array_merge( self::tokens( $piece ), self::tokens( $title ), self::tokens( $title ) ) );
				foreach ( array_keys( $tf ) as $t ) {
					$df[ $t ] = isset( $df[ $t ] ) ? $df[ $t ] + 1 : 1;
				}
				$len      = max( 1, array_sum( $tf ) );
				$total   += $len;
				$chunks[] = array(
					'title' => $title,
					'url'   => $url,
					'text'  => $piece,
					'tf'    => $tf,
					'len'   => $len,
				);
				if ( $i + $size >= $n ) {
					break;
				}
			}
		}
		$index = array(
			'built'  => time(),
			'items'  => count( $query->posts ),
			'chunks' => $chunks,
			'df'     => $df,
			'avgdl'  => $chunks ? $total / count( $chunks ) : 1,
		);
		update_option( NYYO_DOCS_CHAT_INDEX_OPT, $index, false );
		return $index;
	}

	/**
	 * Answer a question: best passages + the best matching sentence.
	 *
	 * @param string $question Question.
	 * @param array  $settings Settings.
	 * @return array
	 */
	public static function answer( $question, $settings ) {
		$index = self::get();
		if ( ! $index ) {
			$index = self::build( $settings );
		}
		$q      = array_unique( self::tokens( $question ) );
		$n      = count( $index['chunks'] );
		$scores = array();
		if ( $q && $n ) {
			foreach ( $index['chunks'] as $i => $c ) {
				$s    = 0.0;
				$seen = 0;
				foreach ( $q as $t ) {
					if ( empty( $c['tf'][ $t ] ) ) {
						continue;
					}
					++$seen;
					$f   = $c['tf'][ $t ];
					$dft = $index['df'][ $t ];
					$idf = log( 1 + ( $n - $dft + 0.5 ) / ( $dft + 0.5 ) );
					$s  += $idf * $f * 2.5 / ( $f + 1.5 * ( 0.25 + 0.75 * $c['len'] / $index['avgdl'] ) );
				}
				// At least half of the question's words must appear in the passage.
				if ( $s > 0 && $seen >= ceil( count( $q ) / 2 ) ) {
					$scores[ $i ] = $s;
				}
			}
		}
		arsort( $scores );
		$top     = $scores ? reset( $scores ) : 0;
		$sources = array();
		foreach ( array_slice( $scores, 0, (int) $settings['max_results'], true ) as $i => $s ) {
			if ( $s < 0.3 * $top ) {
				break;
			}
			$c         = $index['chunks'][ $i ];
			$sources[] = array(
				'title'   => $c['title'],
				'url'     => $c['url'],
				'excerpt' => wp_trim_words( $c['text'], 40, '...' ),
				'score'   => round( $s, 2 ),
			);
		}
		$answer = '';
		if ( $sources ) {
			$best_chunk = $index['chunks'][ array_key_first( $scores ) ];
			$answer     = self::best_sentence( $best_chunk['text'], $q );
		}
		return array(
			'answer'  => $answer,
			'sources' => $sources,
		);
	}

	/**
	 * Pick the sentence of a passage that shares the most words with the question.
	 *
	 * @param string   $text Passage.
	 * @param string[] $q    Question tokens.
	 * @return string
	 */
	private static function best_sentence( $text, $q ) {
		$sentences = preg_split( '/(?<=[.!?])\s+/u', $text );
		$best      = '';
		$best_s    = -1;
		foreach ( $sentences as $sent ) {
			$toks = self::tokens( $sent );
			if ( ! $toks || preg_match( '/\?\s*$/', $sent ) ) {
				continue;
			}
			$ov = count( array_intersect( array_unique( $toks ), $q ) );
			$sc = $ov + $ov / count( $toks );
			if ( $sc > $best_s ) {
				$best_s = $sc;
				$best   = $sent;
			}
		}
		return '' !== $best ? $best : wp_trim_words( $text, 30, '...' );
	}
}
