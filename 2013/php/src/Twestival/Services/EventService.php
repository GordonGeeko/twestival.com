<?php namespace Twestival\Services;

class EventService extends BaseService
{
	function getByContinent($year)
	{
		return $this->container['dao.events']->itemsByLocationName($year, 'CONTINENT');
	}
	function getEvents($year, $active)
	{
		return $this->container['dao.events']->items($year, $active);
	}
	function findPriorRelatedToRegistration($registrationID)
	{
		return $this->container['dao.events']->findPriorRelatedToRegistration($registrationID);
	}
}
?>