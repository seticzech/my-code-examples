<?php

namespace ApiModule\Presenters;

use Entity;
use Exception;
use Facade;
use Kdyby;
use Doctrine;
use Tracy;


class ReportPresenter extends Rest\NetteBridge\Presenter
{

	/**
	 * @var Kdyby\Doctrine\EntityRepository
	 */
	protected $userRepository;


	/**
	 * @var Kdyby\Doctrine\EntityRepository
	 */
	protected $projectRepository;


	/**
	 * @var Kdyby\Doctrine\EntityRepository
	 */
	protected $campaignRepository;


	/**
	 * @var Facade\Export\Suspect\Csv\ProxyFactory
	 */
	protected $csvProxyFactory;


	/**
	 * @var Kdyby\RabbitMq\Producer
	 */
	protected $producer;


	protected $messageParameters = [
		'isFinal' => false,
	];



	public function __construct(Kdyby\RabbitMq\Producer $producer, Doctrine\ORM\EntityManager $entityManager, Facade\Export\Suspect\Csv\ProxyFactory $csvProxyFactory)
	{
		$this->userRepository = $entityManager->getRepository(Entity\User::class);
		$this->projectRepository = $entityManager->getRepository(Entity\Project::class);
		$this->campaignRepository = $entityManager->getRepository(Entity\Campaign::class);
		$this->csvProxyFactory = $csvProxyFactory;
		$this->producer = $producer;
	}



	/**
	 * @inheritDoc
	 */
	protected function verifyAuthorization($resourceName, $resourceAction)
	{
		parent::verifyAuthorization($resourceName, $resourceAction);

		/** @var Entity\Manager|Entity\Client $user */
		$user = $this->userRepository->find($this->getUser()->getId());

		$parameters= $this->request->getParameters();
		$projectId = (int) $parameters['projectId'];
		$campaignId = (int) $parameters['campaignId'];

		/** @var Entity\Project $project */
		$project = $this->projectRepository->find($projectId);
		/** @var Entity\Campaign $campaign */
		$campaign = $this->campaignRepository->find($campaignId);

		if (!$project) {
			$this->sendErrorResponse(Rest\Response\Codes::NOT_FOUND, sprintf('Project identified by %d was not found.', $parameters['projectId']));
		} elseif (!$campaign && isset($parameters['campaignId'])) {
			$this->sendErrorResponse(Rest\Response\Codes::NOT_FOUND, sprintf('Campaign identified by %d was not found.', $parameters['campaignId']));
		} elseif ($project->isActive() === false) {
			$this->sendErrorResponse(Rest\Response\Codes::GONE, sprintf('Project identified by %d is no longer active.', $parameters['projectId']));
		} elseif ($this->getUser()->isInRole(Security\Roles::ADMIN) && $project->getAccount() !== $user->getAccount()) {
			$this->sendErrorResponse(Rest\Response\Codes::FORBIDDEN, sprintf('Admin performing the request does not have access to project identified by %d.', $parameters['projectId']));
		} elseif (($this->getUser()->isInRole(Security\Roles::CLIENT) || $this->getUser()->isInRole(Security\Roles::MANAGER)) && $project !== $user->getProject()) {
			$this->sendErrorResponse(Rest\Response\Codes::FORBIDDEN, sprintf('%s performing the request does not have access to project identified by %d.', ucfirst($user->getRole()), $parameters['projectId']));
		} elseif ($campaign && ($campaign->getProject() !== $project)) {
			$this->sendErrorResponse(Rest\Response\Codes::BAD_REQUEST, sprintf('Campaign identified by %d does not belong to project identified by %d.', $parameters['campaignId'], $parameters['projectId']));
		}
	}



	public function actionCreate($parameters)
	{
		if (isset($parameters['from']) && isset($parameters['to'])) {

			/** @var Entity\Manager|Entity\Client $user */
			$user = $this->userRepository->find($this->getUser()->getId());
			if (!$user->getEmail()) {
				$this->sendErrorResponse(Rest\Response\Codes::UNPROCESSABLE_ENTITY, sprintf('User identified by %d does not have an email address assigned.', $this->getUser()->getId()));
			}

			$project = $this->projectRepository->find((int) $parameters['projectId']);

			try {
				// Used only for validation `from` and `to` parameters and show error to user. Because  we can't send the error message to user in async consumer
				$from = Utils\Time\Factory::create($parameters['from'], 'Y-m-d H:i:s');
				$to = Utils\Time\Factory::create($parameters['to'], 'Y-m-d H:i:s');

				new Utils\Time\DateInterval($from, $to);

				$query = [
					'happenedAt' => $parameters['from'] . ' - ' . $parameters['to'],
				];

				/**
				 * Use current active campaign fields, in case of cross-campaign export
				 * @var Entity\Campaign $campaign
				 */
				$campaign = $this->campaignRepository->find((int) $parameters['campaignId']) ?? $project->getActiveCampaign();

				if (isset($parameters['campaignId'])) {
					$query['campaignId'] = $campaign->getId();
				}

				if (isset($parameters['teamId'])) {
					$query['teamId'] = (string) $parameters['teamId'];
				}

				$this->producer->publish(json_encode(array_merge([
					'fields' => array_map(function(Entity\Identified $field) {
						return $field->getId();
					}, $this->csvProxyFactory->getCampaignFields($campaign)),
					'query' => $query,
					'projectId' => $project->getId(),
					'requestedBy' => $user->getId(),
					'types' => [Suspects\Interactions::REGISTRATION, Suspects\Interactions::SYSTEM, Suspects\Interactions::EXTERNAL],
				], $this->messageParameters)));
			} catch (Exception $e) {
				Tracy\Debugger::log($e);
				$this->sendErrorResponse(Rest\Response\Codes::BAD_REQUEST, Rest\Response\Messages::FORBIDDEN_QUERY);
			}

			$this->sendEmptyResponse(Rest\Response\Codes::ACCEPTED);
		}

		$this->sendErrorResponse(Rest\Response\Codes::FORBIDDEN, Rest\Response\Messages::FORBIDDEN_QUERY);
	}

}
