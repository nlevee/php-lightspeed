<?php

namespace Lightspeed\Engine;

use Lightspeed\InvalidArgumentException;

/**
 * Class Layout
 * @package Lightspeed\Engine
 */
class Layout implements EngineInterface {

	/**
	 * @var EngineInterface
	 */
	protected $engine;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * @var array
	 */
	protected $context;


	/**
	 * $opts = array (
	 * 	template => string
	 * 	context => array
	 * )
	 * @param EngineInterface $engine
	 * @param array $opts (default array())
	 */
	public function __construct(EngineInterface $engine, array $opts = array()) {
		$this->engine = $engine;
		// exports des donnée de config
		if (isset($opts['template']) && is_string($opts['template']))
			$this->template = $opts['template'];
		if (isset($opts['context']) && is_array($opts['context']))
			$this->context = $opts['context'];
	}


	/**
	 * Assign un nouveau template au moteur de rendu
	 * @param string $template
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}


	/**
	 * Assigne une nouvelle key/valeur au context si key est un tableau on assign tout le tableau
	 * @param string|array $key
	 * @param mixed $value (default null)
	 * @throws \Lightspeed\InvalidArgumentException
	 */
	public function setContextValue($key, $value = null) {
		if (is_array($key))
			$this->context = array_merge_recursive($this->context, $key);
		elseif (is_string($key))
			$this->context[$key] = $value;
		else throw new InvalidArgumentException("La clé doit être un tableau key/value ou une chaine valide.");
	}


	/**
	 * Effectue un rendu du template $template avec le $context
	 * @param mixed $template
	 * @param array $context
	 * @return string
	 */
	public function render($template, $context = array()) {
		return $this->engine->render($this->template, array_merge(array(
			'body' => $this->engine->render($template, $context),
		), $this->context));
	}
}