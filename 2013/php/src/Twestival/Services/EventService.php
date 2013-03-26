<?php namespace Twestival\Services;

class EventService extends BaseService
{
	const BASE_URI_TWITTER = 'https://twitter.com/';
	
	function getByContinent($year)
	{
		$events = $this->container['dao.events']->itemsByLocationName($year, 'CONTINENT');
		$this->addUrisToEvents($events);
		return ;
	}
	function getEvent($eventId)
	{
		$event = $this->container['dao.events']->get($eventId);
		$this->addUrisToEvent($event);
		return $event;
	}

	function getEvents($year, $active)
	{
		$events = $this->container['dao.events']->items($year, $active);
		$this->addUrisToEvents($events);
		return $events;
	}
	function findPriorRelatedToRegistration($registrationID)
	{
		$events = $this->container['dao.events']->findPriorRelatedToRegistration($registrationID);
		$this->addUrisToEvents($events);
		return $events;
	}
	
	private function addUrisToEvents(&$events)
	{
		foreach($events as $event)
		{
			$this->addUrisToEvent($event);
		}
	}
	private function addUrisToEvent(&$event)
	{
		$event['TwitterUri'] = EventService::BASE_URI_TWITTER . $event['TwitterName'];
		$event['BlogUri'] = 'http://' 
				. $event['BlogSubdomain']
				. '.' 
				. $this->container['request.domain']
				. $this->container['baseUri'];
		$event['ImageUri'] = $this->getImageUri($event['ImageFilename']);
	}
	function getImagePath()
	{
		return 'img/blog/event/content';
	}
	function getImageUri($imageFilename = '')
	{
		return $this->container['request.protocol']
		. $this->container['request.hostname']
		. $this->container['baseUri']
		. '/'
		. $this->getImagePath()
		. '/'
		. $imageFilename;
	}
	
	function getDefaultEventSettings($year, $registration)
	{
		$name = isset($registration['PreferredTwestivalName'])
		? $registration['PreferredTwestivalName']
		: 'Twestival ' . $registration['City'] . ' ' . $year;
	
		$relatedBlogID = 0;
		$relatedEvents = $this->findPriorRelatedToRegistration($registration['RegistrationID']);
		if($relatedEvents)
		{
			$relatedBlogID = $relatedEvents[0]['BlogID'];
		}
	
		$blogSubdomain = preg_replace('/\s/', '', strtolower($registration['City']));
	
		return array(
				'Name' => $name,
				'Description' => 'Uses social media for social good by connecting communities to highlight a greater cause and have a fun event.',
				'TwitterName' => $registration['TwitterName'],
				'OrganizerEmailAddress' => $registration['EmailAddress'],
				'BlogID' => $relatedBlogID,
				'BlogSubdomain' => $blogSubdomain
		);
	}
	
	function create($registrationID, $blogID, $currentYear, $name, $description, $twitterName, $organizerEmailAddress, $imageFilename, $locationID)
	{
		$eventID = $this->container['dao.events']->create(
				$registrationID,
				$blogID,
				$currentYear,
				$name,
				$description,
				$twitterName,
				$organizerEmailAddress,
				$imageFilename);
		

		$registration = $this->container['service.registration']->get($registrationID);
		
		$password = NULL;
		$this->container['service.login']->createEventAdmin(
				$eventID,
				$registration['TwitterName'],
				$password
		);
		
		$this->container['service.event.teamMember']->save(
				$eventID,
				1,
				$registration['TwitterName']
		);
		
		$this->container['service.location']->saveEventLocationCity(
				$eventID,
				$locationID);
		
		// TODO: Email notification to $oranizerEmailAddress, with direct link to admin (http://$$subdomain.twestival.com/admin), and generated username/password
		
		
		return $eventID;
	}
	
	function saveSiteAdminFields($eventID, $active, $name)
	{
		$this->container['dao.events']->updateEventAdminFields(
				$eventID,
				$active,
				$name
		);
	}
}
?>