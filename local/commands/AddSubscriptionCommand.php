<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class AddSubscriptionCommand extends Command
{
	/**
	 *
	 */
	public function __construct(Campaigns $campaigns, Subscribers $subscribers)
	{
		$this->campaigns   = $campaigns;
		$this->subscribers = $subscribers;

		parent::__construct('campaign:subscription:add');
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
			->addArgument('mobile', InputArgument::REQUIRED, 'Mobile Telephone Number')
		;
	}


	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try {
			$campaign = $this->campaigns->findByName($input->getArgument('campaign'));

			if (!$campaign) {
				throw new \Exception(sprintf(
					'Unable to find campaign "%s", cannot subscribe "%s"',
					$input->getArgument('campaign'),
					$input->getArgument('mobile')
				), 1);
			}

			$this->subscribers->add($campaign, $input->getArgument('mobile'));

		} catch (\Exception $e) {
			$output->writeln($e->getMessage());
			exit($e->getCode());
		}
	}
}
