<?php

use Twilio\Rest\Client as Twilio;

/**
 *
 */
class ReceiveText extends AbstractAction
{
	const MSG_NOT_UNDERSTOOD = 'Sorry, I do not understand what you are saying. Type "unsubscribe" to stop receiving updates.';
	const MSG_SUCCESS = 'Thanks, we will make sure we keep you up to date on this! Type "unsubscribe" to stop receiving updates.';
	const MSG_ALREADY_SUBSCRIBED = 'Thanks. You are already receiving updates from us. Type "unsubscribe" to stop receiving updates.';

	/**
	 *
	 */
	public function __invoke(Campaigns $campaigns, Subscribers $subscribers, Twilio $twilio)
	{
		if ($this->request->getMethod() != 'POST') {
			return $this->response->withStatus(405)->withHeader('Allow', 'POST');
		}

		$body     = trim($this->get('Body'));
		$mobile   = $this->get('From');
		$number   = $this->get('To');
		$campaign = $campaigns->findByKeyword($body);

		if (in_array(strtolower($body), ['unsubscribe', 'stop'])) {
			$campaigns->unsubscribe($mobile);

		} elseif (in_array(strtolower($body), ['start'])) {
			$campaigns->resubscribe($mobile);

		} elseif (!$campaign) {
			$twilio->messages->create(
				$mobile, [
					'from' => $number,
					'body' => static::MSG_NOT_UNDERSTOOD
				]
			);

		} else {
			try {
				$subscribers->add($campaign, $mobile);

				$twilio->messages->create($mobile, [
					'from' => $campaign->number,
					'body' => static::MSG_SUCCESS
				]);

			} catch (\Exception $e) {
				switch($e->getCode()) {
					case 2:
						$twilio->messages->create($mobile, [
							'from' => $campaign->number,
							'body' => static::MSG_ALREADY_SUBSCRIBED
						]);
						break;

					default:
						$twilio->messages->create(
							$mobile, [
								'from' => $campaign->number,
								'body' => $e->getMessage()
							]
						);
						break;
				}
			}
		}

		return $this->response->withStatus(200);
	}
}
