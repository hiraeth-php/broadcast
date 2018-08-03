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
		return preg_replace( '/[^0-9]/', '', $number);
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
