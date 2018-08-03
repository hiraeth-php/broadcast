<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Hiraeth\Application;
use Hiraeth\Configuration;
use MongoDB\Client as MDB;

/**
 *
 */
class CreateCampaignCommand extends Command
{
	/**
	 *
	 */
	public function __construct(Application $app, Configuration $config, MDB $mdb)
	{
		$this->mdb    = $mdb;
		$this->app    = $app;
		$this->config = $config;

		parent::__construct('campaign:create');
	}


	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setDefinition(array())
			->setDescription('No description provided')
			->setHelp(PHP_EOL . $this->getName() . PHP_EOL)
			->addArgument('campaign', InputArgument::REQUIRED, 'Campaign Name')
			->addArgument('number', InputArgument::REQUIRED, 'Campaign Number')
		;
	}


	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$campaign   = $input->getArgument('campaign');
		$number     = $input->getArgument('number');
		$database   = $this->mdb->selectDatabase($this->app->getEnvironment('DB_NAME'));
		$collection = $database->selectCollection('campaigns');

		$result = $collection->findOne([
			'name' => $campaign
		]);

		if ($result) {
			$output->writeln(sprintf('There is already a campaign named "%s"', $campaign));
			exit(1);
		}

		$result = $collection->insertOne([
			'name'        => $campaign,
			'number'      => $number,
			'dateCreated' => new DateTime()
		]);
	}
}
