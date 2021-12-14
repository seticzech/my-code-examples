<?php

namespace Tests\ApiModule\Presenters;

use ApiModule\Presenters;
use Doctrine;
use Entity;
use Nette;
use Nette\Application;
use Tester\Assert;
use Tests;


$container = require_once __DIR__ . '/../../container.php';


class ReportPresenterTest extends NetteBridge\PresenterTestCase
{

	/**
	 * @var Presenters\ReportPresenter
	 */
	private $presenter;


	/**
	 * @var Tests\Utils\ResourceProvider
	 */
	private $resourceProvider;


	/**
	 * @var Doctrine\ORM\EntityManager
	 */
	private $entityManager;



	public function __construct(Presenters\ReportPresenter $presenter, Doctrine\ORM\EntityManager $entityManager, Tests\Utils\ResourceProvider $resourceProvider)
	{
		$this->entityManager = $entityManager;
		$this->presenter = $presenter;
		$this->resourceProvider = $resourceProvider;
	}



	protected function setUp()
	{
		parent::setUp();

		$this->presenter->injectPrimary($this->requestParser, $this->configuration, $this->authenticator, $this->linkFactory, $this->user, $this->httpRequest, $this->httpResponse);

		Tests\Utils::lockDatabase();
	}



	public function testInvalidProject()
	{
		$this->user->expects('getId')->andReturn($this->resourceProvider->getValidManager()->getId());

		$campaign = $this->resourceProvider->getValidCampaign();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::CREATE,
			'projectId' => -1,
			'campaignId' => $campaign->getId(),
		]);
		$response = $this->presenter->run($request);

		Assert::equal(Rest\Response\Codes::NOT_FOUND, $response->getCode());
	}



	public function testInvalidCampaign()
	{
		$this->user->expects('getId')->andReturn($this->resourceProvider->getValidManager()->getId());

		$project = $this->resourceProvider->getValidProject();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::CREATE,
			'projectId' => $project->getId(),
			'campaignId' => -1,
		]);
		$response = $this->presenter->run($request);

		Assert::equal(Rest\Response\Codes::NOT_FOUND, $response->getCode());
	}



	public function testInactiveProject()
	{
		$this->user->expects('getId')->andReturn($this->resourceProvider->getValidManager()->getId());

		$project = $this->resourceProvider->getValidProject();
		$project->setActive(false);

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::CREATE,
			'projectId' => $project->getId(),
			'campaignId' => $project->getActiveCampaign()->getId(),
		]);
		$response = $this->presenter->run($request);

		$project->setActive(true);

		Assert::equal(Rest\Response\Codes::GONE, $response->getCode());
	}



	public function testProjectFromOtherAccountForAdmins()
	{
		$this->user->expects('getId')->andReturn($this->resourceProvider->getValidManager()->getId());
		$this->user->expects('isInRole')->with(Security\Roles::ADMIN)->andReturn(true);
		$this->user->expects('isInRole')->with(Security\Roles::CLIENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::AGENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::MANAGER)->andReturn(false);

		$account = new Entity\Account('Company X');
		$project = new Entity\Project('Project Y', $account);
		$project->setActive(true);
		$campaign = new Entity\Campaign('Campaign Z', $project);

		$this->entityManager->persist($account);
		$this->entityManager->persist($project);
		$this->entityManager->persist($campaign);
		$this->entityManager->flush();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::CREATE,
			'projectId' => $project->getId(),
			'campaignId' => $campaign->getId(),
		]);
		$response = $this->presenter->run($request);

		Assert::equal(Rest\Response\Codes::FORBIDDEN, $response->getCode());

		$this->entityManager->remove($campaign);
		$this->entityManager->remove($project);
		$this->entityManager->remove($account);
		$this->entityManager->flush();
	}



	public function testDifferentProjectForManagers()
	{
		$manager = $this->resourceProvider->getValidManager();
		$this->user->expects('getId')->andReturn($manager->getId());
		$this->user->expects('isInRole')->with(Security\Roles::ADMIN)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::CLIENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::AGENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::MANAGER)->andReturn(true);

		$project = new Entity\Project('Project Y', $manager->getAccount());
		$project->setActive(true);
		$campaign = new Entity\Campaign('Campaign Z', $project);

		$this->entityManager->persist($project);
		$this->entityManager->persist($campaign);
		$this->entityManager->flush();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::GET,
			'projectId' => $project->getId(),
			'campaignId' => $campaign->getId(),
		]);
		$response = $this->presenter->run($request);

		$this->entityManager->remove($campaign);
		$this->entityManager->remove($project);
		$this->entityManager->flush();

		Assert::equal(Rest\Response\Codes::FORBIDDEN, $response->getCode());
	}



	public function testDifferentProjectForClients()
	{
		$client = $this->resourceProvider->getValidClient();
		$this->user->expects('getId')->andReturn($client->getId());
		$this->user->expects('isInRole')->with(Security\Roles::ADMIN)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::CLIENT)->andReturn(true);
		$this->user->expects('isInRole')->with(Security\Roles::AGENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::MANAGER)->andReturn(false);

		$project = new Entity\Project('Project Y', $client->getAccount());
		$project->setActive(true);
		$campaign = new Entity\Campaign('Campaign Z', $project);

		$this->entityManager->persist($project);
		$this->entityManager->persist($campaign);
		$this->entityManager->flush();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::GET,
			'projectId' => $project->getId(),
			'campaignId' => $campaign->getId(),
		]);
		$response = $this->presenter->run($request);

		$this->entityManager->remove($campaign);
		$this->entityManager->remove($project);
		$this->entityManager->flush();

		Assert::equal(Rest\Response\Codes::FORBIDDEN, $response->getCode());
	}



	public function testCampaignFromOtherProject()
	{
		$this->user->expects('getId')->andReturn($this->resourceProvider->getValidManager()->getId());
		$this->user->expects('isInRole')->with(Security\Roles::ADMIN)->andReturn(true);
		$this->user->expects('isInRole')->with(Security\Roles::CLIENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::AGENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::MANAGER)->andReturn(false);

		$account = new Entity\Account('Company X');
		$project = new Entity\Project('Project Y', $account);
		$project->setActive(true);
		$campaign = new Entity\Campaign('Campaign Z', $project);

		$this->entityManager->persist($account);
		$this->entityManager->persist($project);
		$this->entityManager->persist($campaign);
		$this->entityManager->flush();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::CREATE,
			'projectId' => $this->resourceProvider->getValidProject()->getId(),
			'campaignId' => $campaign->getId(),
		]);
		$response = $this->presenter->run($request);

		Assert::equal(Rest\Response\Codes::BAD_REQUEST, $response->getCode());

		$this->entityManager->remove($campaign);
		$this->entityManager->remove($project);
		$this->entityManager->remove($account);
		$this->entityManager->flush();
	}



	public function testMissingFromArgument()
	{
		$this->user->expects('getId')->andReturn($this->resourceProvider->getValidManager()->getId());
		$this->user->expects('isInRole')->with(Security\Roles::ADMIN)->andReturn(true);
		$this->user->expects('isInRole')->with(Security\Roles::CLIENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::AGENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::MANAGER)->andReturn(false);

		$project = $this->resourceProvider->getValidProject();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::CREATE,
			'projectId' => $project->getId(),
			'campaignId' => $project->getActiveCampaign()->getId(),
			'to' => '2015-02-09',
		]);
		$response = $this->presenter->run($request);

		Assert::equal(Rest\Response\Codes::FORBIDDEN, $response->getCode());
	}



	public function testMissingToArgument()
	{
		$this->user->expects('getId')->andReturn($this->resourceProvider->getValidManager()->getId());
		$this->user->expects('isInRole')->with(Security\Roles::ADMIN)->andReturn(true);
		$this->user->expects('isInRole')->with(Security\Roles::CLIENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::AGENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::MANAGER)->andReturn(false);

		$project = $this->resourceProvider->getValidProject();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::CREATE,
			'projectId' => $project->getId(),
			'campaignId' => $project->getActiveCampaign()->getId(),
			'from' => '2015-02-09',
		]);
		$response = $this->presenter->run($request);

		Assert::equal(Rest\Response\Codes::FORBIDDEN, $response->getCode());
	}



	public function testInvalidFromArgument()
	{
		$this->user->expects('getId')->andReturn($this->resourceProvider->getValidManager()->getId());
		$this->user->expects('isInRole')->with(Security\Roles::ADMIN)->andReturn(true);
		$this->user->expects('isInRole')->with(Security\Roles::CLIENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::AGENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::MANAGER)->andReturn(false);

		$project = $this->resourceProvider->getValidProject();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::CREATE,
			'projectId' => $project->getId(),
			'campaignId' => $project->getActiveCampaign()->getId(),
			'from' => 'invalid',
			'to' => '2015-02-09',
		]);
		$response = $this->presenter->run($request);

		Assert::equal(Rest\Response\Codes::BAD_REQUEST, $response->getCode());
	}



	public function testInvalidToArgument()
	{
		$this->user->expects('getId')->andReturn($this->resourceProvider->getValidManager()->getId());
		$this->user->expects('isInRole')->with(Security\Roles::ADMIN)->andReturn(true);
		$this->user->expects('isInRole')->with(Security\Roles::CLIENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::AGENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::MANAGER)->andReturn(false);

		$project = $this->resourceProvider->getValidProject();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::CREATE,
			'projectId' => $project->getId(),
			'campaignId' => $project->getActiveCampaign()->getId(),
			'from' => '2015-02-09',
			'to' => 'invalid',
		]);
		$response = $this->presenter->run($request);

		Assert::equal(Rest\Response\Codes::BAD_REQUEST, $response->getCode());
	}



	public function testUserRoleWithoutEmail()
	{
		$manager = $this->resourceProvider->getValidManager();
		$originalEmail = $manager->getEmail();
		$manager->setEmail('');

		$this->user->expects('getId')->andReturn($manager->getId());
		$this->user->expects('isInRole')->with(Security\Roles::ADMIN)->andReturn(true);
		$this->user->expects('isInRole')->with(Security\Roles::CLIENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::AGENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::MANAGER)->andReturn(false);

		$project = $this->resourceProvider->getValidProject();
		$campaign = $project->getActiveCampaign();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::CREATE,
			'projectId' => $project->getId(),
			'campaignId' => $campaign->getId(),
			'from' => '2014-01-01',
			'to' => '2014-01-03',
		]);
		$response = $this->presenter->run($request);

		$manager->setEmail($originalEmail);

		Assert::equal(Rest\Response\Codes::UNPROCESSABLE_ENTITY, $response->getCode());
	}



	public function testValid()
	{
		$this->user->expects('getId')->andReturn($this->resourceProvider->getValidManager()->getId());
		$this->user->expects('isInRole')->with(Security\Roles::ADMIN)->andReturn(true);
		$this->user->expects('isInRole')->with(Security\Roles::CLIENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::AGENT)->andReturn(false);
		$this->user->expects('isInRole')->with(Security\Roles::MANAGER)->andReturn(false);

		$project = $this->resourceProvider->getValidProject();
		$campaign = $project->getActiveCampaign();

		$request = new Application\Request('Api:Report', 'POST', [
			'action' => Rest\Request\Actions::CREATE,
			'projectId' => $project->getId(),
			'campaignId' => $campaign->getId(),
			'from' => '2014-01-01 10:00:00',
			'to' => '2014-01-03 22:00:00',
		]);
		$response = $this->presenter->run($request);

		Assert::equal(Rest\Response\Codes::ACCEPTED, $response->getCode());
	}

}


run(new ReportPresenterTest($container->getByType(Nette\Application\IPresenterFactory::class)->createPresenter('Api:Report'), $container->getByType(Doctrine\ORM\EntityManager::class), $container->getByType(Tests\Utils\ResourceProvider::class)));
