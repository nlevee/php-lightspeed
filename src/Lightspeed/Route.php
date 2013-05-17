<?php
/**
 * @author: Nicolas Levée
 * @version 160520131858
 */

namespace Lightspeed;

/**
 * Class Route
 * @package Lightspeed
 */
class Route {

	/**
	 * @var string
	 */
	private $pattern_uri = '';

	/**
	 * @var array
	 */
	private $filters = array();

	/**
	 * Construction d'une nouvelle route
	 * @param string $pattern_uri
	 * @param array $filters
	 */
	public function __construct($pattern_uri, array $filters = array()) {
		$this->pattern_uri = $pattern_uri;
		$this->filters = $filters;
	}

	/**
	 * Check d'une $request_uri avec la route
	 * @param string $request_uri
	 * @return array|bool
	 */
	public function match($request_uri) {
		// cas de la regle générale
		if ($this->pattern_uri == '*')
			return array();

		// récuperation des variables
		$p_values = $p_names = array();
		preg_match_all('@:([\w]+)@', $this->pattern_uri, $p_names, PREG_PATTERN_ORDER);
		$p_names = $p_names[0];

		// remplace
		$pattern_uri_regex = preg_replace_callback('@:[\w]+@', array($this, 'pattern_regex'), $this->pattern_uri);
		$pattern_uri_regex .= '/?';

		// application de la regle
		$params = array();
		if (preg_match('@^' . $pattern_uri_regex . '$@', $request_uri, $p_values) > 0) {
			array_shift($p_values);

			// lecture des params
			foreach ($p_names as $index => $value)
				$params[substr($value, 1)] = urldecode($p_values[$index]);

			// renvoi des données récupéré dans l'url
			return $params;
		}

		// pas de correspondance
		return false;
	}

	/**
	 * Permet de transformer le pattern en regex
	 * @param array $matches
	 * @return string
	 */
	protected function pattern_regex(array $matches) {
		// si il y a filters on l'applique sur la clé trouvé
		$key = str_replace(':', '', $matches[0]);
		if (array_key_exists($key, $this->filters)) {
			return '(' . $this->filters[$key] . ')';
		}
		return '([a-zA-Z0-9_\+\-%]+)';
	}

}