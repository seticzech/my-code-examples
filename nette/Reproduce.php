<?php

namespace Console\Command\Interactions;

use Dao;
use DateTime;
use Doctrine;
use Entity;
use Fixtures;
use Kdyby;
use LogicException;
use Symfony;


class Reproduce extends Symfony\Component\Console\Command\Command
{

	const DATE_FORMAT_WITHOUT_TIME = '/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/';



	/**
	 * @var Doctrine\ORM\EntityRepository
	 */
	private $projectRepository;


	/**
	 * @var Doctrine\ORM\EntityRepository
	 */
	private $interactionCategoryRepository;


	/**
	 * @var Dao\Interaction
	 */
	private $interactionDao;


	/**
	 * @var Kdyby\RabbitMq\Producer
	 */
	private $producer;



	public function __construct(Kdyby\RabbitMq\Producer $producer, Kdyby\Doctrine\EntityManager $entityManager, Dao\Interaction $interactionDao)
	{
		parent::__construct();

		$this->projectRepository = $entityManager->getRepository(Entity\Project::class);
		$this->interactionCategoryRepository = $entityManager->getRepository(Entity\Interaction\Category::class);
		$this->interactionDao = $interactionDao;
		$this->producer = $producer;
	}



	protected function configure()
	{
		$this
			->setName('app:interactions:reproduce')
			->addOption('projectId', 'p', Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Identifier of the project.')
			->addOption('status', 's', Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Filter interactions by status.')
			->addArgument('from', Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Day in format Y-m-d H:i:s.')
			->addArgument('to', Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Day in format Y-m-d.', date('Y-m-d H:i:s'));
	}



	protected function execute(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)
	{
		if (preg_match(self::DATE_FORMAT_WITHOUT_TIME, $input->getArgument('from'))) {
			$from = DateTime::createFromFormat('Y-m-d', $input->getArgument('from'));
			$from->setTime(0, 0, 0);
		} else {
			$from = DateTime::createFromFormat('Y-m-d H:i:s', $input->getArgument('from'));
		}

		if (preg_match(self::DATE_FORMAT_WITHOUT_TIME, $input->getArgument('to'))) {
			$to = DateTime::createFromFormat('Y-m-d', $input->getArgument('to'));
			$to->setTime(23, 59, 59);
		} else {
			$to = DateTime::createFromFormat('Y-m-d H:i:s', $input->getArgument('to'));
		}

		if (!$from) {
			$output->writeln('<error>"From" date was provided in incorrect format.</error>');

			return 1;
		}

		if (!$to) {
			$output->writeln('<error>"To" date was provided in incorrect format.</error>');

			return 1;
		}

		if ($from > $to) {
			$output->writeln('<error>From should be lower date than to.</error>');

			return 1;
		}

		/** @var Entity\Project $project */
		$project = $this->projectRepository->find($input->getOption('projectId'));
		if (!$project) {
			$output->writeln(sprintf('<error>Project with identifier %d does not exist.</error>', $input->getOption('projectId')));

			return 1;
		}

		$referenceChain = new Fixtures\ReferenceChain($project->getActiveCampaign()->getFields());
		$statusReference = 'status:' . $input->getOption('status');
		$statusValue = null;

		if ($statusReference) {
			try {
				$statusValue = $referenceChain->findValueByPath($statusReference);
				if (!$statusValue) {
					$output->writeln(sprintf('<error>Status that filter interactions referenced by %s does not exist.</error>', $input->getOption('status')));

					return 1;
				}
			} catch (LogicException $e) {
				$output->writeln(sprintf('<error>Status that filter interactions referenced by %s does not exist.</error>', $input->getOption('status')));

				return 1;
			}
		}

		$interactionCategories = $this->interactionCategoryRepository->findBy([
			'value' => $statusValue->getId(),
		]);

		if (!$interactionCategories) {
			$output->writeln(sprintf('<error>Interaction category for the %s status does not exist.</error>', $input->getOption('status')));

			return 1;
		}

		$categoryIds = array_map(function(Entity\Interaction\Category $category) {
			return $category->getId();
		}, $interactionCategories);

		$interactions = $this->interactionDao->getCollection()->find([
			Keys::INTERACTION . '.categoryId' => [
				'$in' => $categoryIds,
			],
			'happenedAt' => [
				'$gt' => Mongo\DateTime::fromDateTime($from),
				'$lt' => Mongo\DateTime::fromDateTime($to),
			],
		])->toArray();

		if (!$interactions) {
			$output->writeln(sprintf('<error>Interaction for the %s status does not exist.</error>', $input->getOption('status')));

			return 1;
		}

		foreach ($interactions as $interaction) {
			$suspect = new Entity\Suspect(Suspects\Postprocessor::prepareFromInteraction($interaction));

			$this->producer->publish(json_encode([
				'projectId' => $project->getId(),
				'campaignId' => $project->getActiveCampaign()->getId(),
				'suspectId' => (string)$suspect->getId(),
				// This heavily depends on the fact that initial interaction with data will be done by real user (manager, agent)
				'userId' => $suspect->getAttributes()[Keys::USER]['id'],
				'data' => Suspect\Serializer::serialize($suspect->getAttributes()),
			]));
		}
	}

}
