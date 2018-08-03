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
class AddKeywordCommand extends Command
{
	/**
	 *
	 */
	public function __construct(Application $app, Configuration $config, MDB $mdb)
	{
		$this->mdb    = $mdb;
		$this->app    = $app;
		$this->config = $config;

		parent::__construct('campaign:keyword:add');
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
			->addArgument('keyword', InputArgument::REQUIRED, 'Keyword')
		;
	}


	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$campaign   = $input->getArgument('campaign');
		$keyword    = $input->getArgument('keyword');
		$database   = $this->mdb->selectDatabase($this->app->getEnvironment('DB_NAME'));
		$collection = $database->selectCollection('campaigns');

		$result = $collection->findOne([
			'name' => $campaign
		]);

		if (!$result) {
			$output->writeln(sprintf('There is no campaign named "%s"', $campaign));
			exit(1);
		}

		$keywords = $result->keywords ?? array();
		$result   = $collection->updateOne($result, [
			'$set' => [
				'keywords' => array_unique(array_merge((array) $keywords, [$keyword]))
			]
		]);
	}
}
