<?php

/**
 *
 */
class Subscribers
{
	/**
	 *
	 */
	static public function cleanPhone($number)
	{
		$number = trim($number);

		if (strpos($number, '+') === 0) {
			$has_country = TRUE;
		} else {
			$has_country = FALSE;
		}

		$number = preg_replace( '/[^0-9]/', '', $number);

		if (strlen($number) == 10 && !$has_country) {
			return '+1' . $number;
		} else {
			return '+' . $number;
		}
	}


	/**
	 *
	 */
	public function __construct(Campaigns $campaigns)
	{
		$this->campaigns = $campaigns;
	}


	/**
	 *
	 */
	public function add($campaign, $mobile)
	{
		$mobile     = static::cleanPhone($mobile);
		$collection = $this->campaigns->getCollection($campaign);
		$result     = $collection->findOne([
			'mobile' => $mobile
		]);

		if (!$result) {
			$collection->insertOne(['mobile' => $mobile]);

		} else {
			if (empty($result->noSMS)) {
				throw new \Exception(sprintf(
					'The campaign "%s" already has "%s" subscribed',
					$campaign->name,
					$mobile
				), 2);
			}

			$collection->updateOne($result, ['$set' => ['noSMS' => FALSE]]);
		}
	}
}
