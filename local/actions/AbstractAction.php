<?php

use Hiraeth\Application;
use Hiraeth\Chariot\Resolver;

/**
 *
 */
abstract class AbstractAction
{
	/**
	 *
	 */
	public function __construct(Application $app, Resolver $resolver)
	{
		$this->app       = $app;
		$this->router    = $resolver->getRouter();
		$this->request   = $resolver->getRequest();
		$this->response  = $resolver->getResponse();
	}


	/**
	 *
	 */
	public function get($name = NULL, $default = NULL)
	{
		if (!$name) {
			return $this->request->getParsedBody()
				+  $this->request->getUploadedFiles()
				+  $this->request->getQueryParams();
		}

		if (isset($this->request->getParsedBody()[$name])) {
			$value = $this->request->getParsedBody()[$name];

		} elseif (isset($this->request->getUploadedFiles()[$name])) {
			$value = $this->request->getUploadedFiles()[$name];

		} elseif (isset($this->request->getQueryParams()[$name])) {
			$value = $this->request->getQueryParams()[$name];

		} else {
			return $default;
		}

		if ($default !== NULL && !is_object($default)) {
			settype($value, gettype($default));
		}

		return $value;
	}


	/**
	 *
	 */
	public function has($name)
	{
		return array_key_exists($name, $this->request->getParsedBody())
			|| array_key_exists($name, $this->request->getUploadedFiles())
			|| array_key_exists($name, $this->request->getQueryParams());
	}
}
