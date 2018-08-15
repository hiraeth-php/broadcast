<?php

use Twilio\Rest\Client as Twilio;

/**
 *
 */
class Subscribe extends AbstractAction
{
	const MSG_SUCCESS = 'Thanks, we will make sure we keep you up to date on this! Type "unsubscribe" to stop receiving updates.';
	const MSG_ALREADY_SUBSCRIBED = 'Thanks. You are already receiving updates from us. Type "unsubscribe" to stop receiving updates.';

	/**
	 *
	 */
	public function __invoke(Campaigns $campaigns, Subscribers $subscribers, Twilio $twilio, $keyword)
	{
		if ($this->request->getMethod() != 'POST') {
			return $this->response->withStatus(405)->withHeader('Allow', 'POST');
		}

		$mobile   = $this->get('mobile');
		$campaign = $campaigns->findByKeyword($keyword);

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

					return $this->response
						->withStatus(303)
						->withHeader('Location', $campaign->redirect . '?error')
					;

					break;
			}
		}

		return $this->response
			->withStatus(303)
			->withHeader('Location', $campaign->redirect . '?thanks')
		;
	}
}
