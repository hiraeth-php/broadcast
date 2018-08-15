<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twilio\Rest\Client as Twilio;

/**
 *
 */
class SendMessageCommand extends Command
{
	/**
	 *
	 */
	public function __construct(Campaigns $campaigns, Subscribers $subscribers, Twilio $twilio)
	{
		$this->campaigns   = $campaigns;
		$this->twilio      = $twilio;

		parent::__construct('campaign:message:send');
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
			->addArgument('message_file', InputArgument::REQUIRED, 'Message File')
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
					'Unable to find campaign "%s", cannot send message.',
					$input->getArgument('campaign')
				), 1);
			}

			$collection = $this->campaigns->getCollection($campaign);
			$template   = file_get_contents($input->getArgument('message_file'));

			if (!$template) {
				throw new \Exception(sprintf(
					'Unable to open message template, bad file or file is empty',
					$input->getArgument('message_file')
				), 3);
			}

			foreach ($collection->find() as $subscriber) {
				if (!empty($subscriber->noSMS)) {
					continue;
				}

				$message = $template;

				foreach ($subscriber as $property => $value) {
					$message = str_replace('{' . $property . '}', $value, $message);
				}

				try {
					$this->twilio->messages->create(
						$subscriber->mobile, [
							'from' => $campaign->number,
							'body' => $message
						]
					);

					$output->writeln('Message sent to ' . $subscriber->mobile);
					sleep(1);

				} catch (\Exception $e) {
					if ($e->getCode() == 21610) {
						$this->campaigns->unsubscribe($subscriber->mobile);
						$output->writeln(sprintf(
							'Could not send message to %s: Unsubscribed',
							$subscriber->mobile
						));

					} else {
						$output->writeln(sprintf(
							'Could not send message to %s: %s',
							$subscriber->mobile,
							$e->getMessage()
						));
					}
				}
			}

		} catch (\Exception $e) {
			$output->writeln($e->getMessage());
			exit($e->getCode());
		}
	}
}
