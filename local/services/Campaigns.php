<?php

use MongoDB\Client as MDB;
use Hiraeth\Application;

/**
 *
 */
class Campaigns
{
	/**
	 *
	 */
	public function __construct(MDB $mdb, Application $app)
	{
		$this->mdb = $mdb;
		$this->db  = $mdb->selectDatabase($app->getEnvironment('DB_NAME'));
	}


	/**
	 *
	 */
	public function findByKeyword($keyword)
	{
		$keyword = strtolower($keyword);

		return $this->getCollection()->findOne(['keywords' => $keyword]);
	}


	/**
	 *
	 */
	public function findByName($name)
	{
		$name = strtolower($name);

		return $this->getCollection()->findOne(['name' => $name]);

	}


	/**
	 *
	 */
	public function getCollection($campaign = NULL)
	{
		if ($campaign) {
			return $this->db->selectCollection('campaign_' . $campaign->name);
		} else {
			return $this->db->selectCollection('campaigns');
		}
	}


	/**
	 *
	 */
	public function hasByName($campaign)
	{
		return (bool) $this->getCollection()->findByName($campaign->name);
	}


	/**
	 *
	 */
	public function resubscribe($mobile)
	{
		foreach ($this->getCollection()->find() as $campaign) {
			$collection = $this->getCollection($campaign);
			$subscriber = $collection->findOne(['mobile' => $mobile]);

			if ($subscriber) {
				$collection->updateOne($subscriber, ['$set' => ['noSMS' => FALSE]]);
			}
		}
	}


	/**
	 *
	 */
	public function unsubscribe($mobile)
	{
		foreach ($this->getCollection()->find() as $campaign) {
			$collection = $this->getCollection($campaign);
			$subscriber = $collection->findOne(['mobile' => $mobile]);

			if ($subscriber) {
				$collection->updateOne($subscriber, ['$set' => ['noSMS' => TRUE]]);
			}
		}
	}
}
