<?php

use Twilio\Rest\Client as Twilio;

/**
 *
 */
class TwilioDelegate implements Hiraeth\Delegate
{
	/**
	 *
	 */
	protected $app = NULL;


	/**
	 *
	 */
	protected $config = NULL;


	/**
	 * Get the class for which the delegate operates.
	 *
	 * @static
	 * @access public
	 * @return string The class for which the delegate operates
	 */
	static public function getClass()
	{
		return Twilio::class;
	}


	/**
	 * Get the interfaces for which the delegate operates.
	 *
	 * @static
	 * @access public
	 * @return array A list of interfaces for which the delegate provides a class
	 */
	static public function getInterfaces()
	{
		return [];
	}


	/**
	 *
	 */
	public function __construct(Hiraeth\Application $app, Hiraeth\Configuration $config)
	{
		$this->app       = $app;
		$this->config    = $config;
	}


	/**
	 * Get the instance of the class for which the delegate operates.
	 *
	 * @access public
	 * @param Broker $broker The dependency injector instance
	 * @return Object The instance of the class for which the delegate operates
	 */
	public function __invoke(Hiraeth\Broker $broker)
	{
		return new Twilio(
			$this->app->getEnvironment('TWILIO_SID'),
			$this->app->getEnvironment('TWILIO_TOKEN')
		);
	}
}
