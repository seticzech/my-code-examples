<?php

namespace Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;


/**
 * @ORM\Entity
 * @ORM\Table(name = "imports")
 * @method string getStatus()
 * @method integer|null getProcessId()
 * @method int getCurrentLine()
 * @method int|null getTotalLinesCount()
 * @method int|null getRejectedLinesCount()
 * @method DateTime getRequestedAt()
 * @method DateTime|null getStartedAt()
 * @method DateTime|null getFinishedAt()
 * @method User getUser()
 * @method Project getProject()
 * @method File getInputFile()
 * @method File|null getRejectionsFile()
 */
class Import extends Identified implements JsonSerializable
{

	use Immutable;



	/** Percentage of addresses needed for import to be considered successful */
	const SUCCESS_THRESHOLD = 50;



	/**
	 * @ORM\Column(type = "string", length = 16)
	 * @var string
	 */
	protected $status;


	/**
	 * @ORM\Column(type = "integer", name = "current_line", nullable = true, options = {"unsigned" = true})
	 * @var int|null
	 */
	protected $currentLine;


	/**
	 * @ORM\Column(type = "integer", name = "input_file_lines", options = {"unsigned" = true}, nullable = true)
	 * @var int
	 */
	protected $totalLinesCount;


	/**
	 * @ORM\OneToOne(targetEntity = "File", cascade = {"persist"})
	 * @ORM\JoinColumn(name = "input_file_id")
	 * @var File
	 */
	protected $inputFile;


	/**
	 * @ORM\OneToOne(targetEntity = "File", cascade = {"persist"})
	 * @ORM\JoinColumn(name = "rejections_file_id")
	 * @var File
	 */
	protected $rejectionsFile;


	/**
	 * @ORM\Column(type = "integer", name = "rejections_file_lines", options = {"unsigned" = true}, nullable = true)
	 * @var int
	 */
	protected $rejectedLinesCount;


	/**
	 * @ORM\Column(type = "datetime", name = "requested_at")
	 * @var DateTime
	 */
	protected $requestedAt;


	/**
	 * @ORM\Column(type = "datetime", name = "started_at", nullable = true)
	 * @var DateTime
	 */
	protected $startedAt;


	/**
	 * @ORM\Column(type = "datetime", name = "finished_at", nullable = true)
	 * @var DateTime
	 */
	protected $finishedAt;


	/**
	 * @ORM\Column(type = "integer", nullable = true)
	 * @var integer|null
	 */
	protected $processId;


	/**
	 * @ORM\ManyToOne(targetEntity = "User")
	 * @ORM\JoinColumn(nullable = false)
	 * @var User
	 */
	protected $user;


	/**
	 * @ORM\ManyToOne(targetEntity = "Project")
	 * @ORM\JoinColumn(nullable = false)
	 * @var Project
	 */
	protected $project;


	/**
	 * Determines whether import entity is directly present in suspect collection and thus can be used for search
	 * @ORM\Column(type = "boolean", name = "is_connected_to_suspects", options = {"default": false})
	 * @var bool
	 */
	protected $isConnectedToSuspects;



	/**
	 * @param File $inputFile
	 * @param User $user
	 * @param Project $project
	 */
	public function __construct(File $inputFile, User $user, Project $project)
	{
		parent::__construct();
		$this->inputFile = $inputFile;
		$this->setCurrentState(Tasks\Status::CREATED);
		$this->currentLine = 0;
		$this->rejectedLinesCount = null;
		$this->totalLinesCount = null;
		$this->user = $user;
		$this->project = $project;
		$this->processId = null;
		$this->isConnectedToSuspects = false;
	}



	/**
	 * @param int $amountOfLines
	 */
	public function setTotalLinesCount($amountOfLines)
	{
		if (!is_null($this->totalLinesCount)) {
			throw new Application\LogicException('It\'s not possible to modify already set total lines count.');
		}

		$this->totalLinesCount = (int) $amountOfLines;
	}



	/**
	 * @deprecated
	 */
	public function setCurrentLine($line)
	{
		throw new Application\LogicException(sprintf('It\'s not possible to modify current line directly, use %s::setCurrentState instead', __CLASS__));
	}



	/**
	 * @deprecated
	 */
	public function setRequestedAt($requestedAt)
	{
		throw new Application\LogicException(sprintf('It\'s not possible to modify requested at directly, use %s::setCurrentState instead', __CLASS__));
	}



	/**
	 * @deprecated
	 */
	public function setStartedAt($startedAt)
	{
		throw new Application\LogicException(sprintf('It\'s not possible to modify started at directly, use %s::setCurrentState instead', __CLASS__));
	}



	/**
	 * @deprecated
	 */
	public function setFinishedAt($finishedAt)
	{
		throw new Application\LogicException(sprintf('It\'s not possible to modify finished at directly, use %s::setCurrentState instead', __CLASS__));
	}



	/**
	 * @param integer $processId
	 */
	public function setProcessId($processId)
	{
		$this->processId = (int) $processId;
	}



	public function setConnectedToSuspects(bool $isConnectedToSuspects = true)
	{
		$this->isConnectedToSuspects = $isConnectedToSuspects;
	}



	public function isConnectedToSuspects(): bool
	{
		return $this->isConnectedToSuspects;
	}



	/**
	 * @param File $file
	 * @return $this
	 * @throws Application\LogicException If rejection file path is already set
	 */
	public function setRejectionsFile(File $file)
	{
		if (!is_null($this->rejectionsFile)) {
			throw new Application\LogicException('It\'s not possible to modify already set rejection file');
		}

		$this->rejectionsFile = $file;

		return $this;
	}



	/**
	 * @param int $amountOfLines
	 * @return $this
	 * @throws Application\LogicException If rejection lines count is already set
	 */
	public function setRejectedLinesCount($amountOfLines)
	{
		if (!is_null($this->rejectedLinesCount)) {
			throw new Application\LogicException('It\'s not possible to modify already set rejection lines count');
		}

		$this->rejectedLinesCount = (int) $amountOfLines;

		return $this;
	}



	/**
	 * @return bool
	 */
	public function isBeingProcessed()
	{
		return is_integer($this->processId) && $this->processId > 0 && ($this->status === Tasks\Status::STARTED || $this->status === Tasks\Status::PROGRESSING);
	}



	/**
	 * @return bool
	 */
	public function wasSuccessful()
	{
		if ($this->getPercentageOfRejectedLines() !== null) {
			return $this->getPercentageOfRejectedLines() < self::SUCCESS_THRESHOLD;
		}

		return false;
	}



	/**
	 * @return float|null
	 */
	public function getPercentageOfRejectedLines()
	{
		if ($this->getTotalLinesCount()) {
			$onePercent = $this->getTotalLinesCount() / 100;

			return round($this->getRejectedLinesCount() / $onePercent, $precision = 2);
		}

		return null;
	}



	/**
	 * @param string $status
	 * @param int|null $currentLine
	 */
	public function setCurrentState($status, $currentLine = null)
	{
		$this->currentLine = $currentLine !== null ? (int) $currentLine : null;
		$this->status = (string) $status;

		switch ($status) {
			case Tasks\Status::CREATED:
				$this->requestedAt = new DateTime();
				break;
			case Tasks\Status::STARTED:
				$this->startedAt = new DateTime();
				// Useful when import is rejected the first time during async processing
				$this->rejectedLinesCount = null;
				$this->rejectionsFile = null;
				break;
			case Tasks\Status::ABORTED:
			case Tasks\Status::FAILED:
			case Tasks\Status::SUCCEEDED:
				$this->finishedAt = new DateTime();
				break;
		}
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->getInputFile()->getName() . ' from ' . $this->requestedAt->format('m/d/Y');
	}



	/**
	 * @inheritDoc
	 */
	public function jsonSerialize()
	{
		return [
			'id' => $this->getId(),
			'status' => $this->status,
			'currentLine' => $this->currentLine,
			'totalLinesCount' => $this->totalLinesCount,
			'inputFileId' => $this->inputFile->getId(),
			'inputFileName' => $this->inputFile->getName(),
			'rejectedLinesCount' => $this->rejectedLinesCount,
			'rejectionsFileId' => $this->rejectionsFile ? $this->rejectionsFile->getId() : null,
			'rejectionsFileName' => $this->rejectionsFile ? $this->rejectionsFile->getName() : null,
			'requestedAt' => Time\RFCDecorator::toHttpString($this->requestedAt),
			'startedAt' => $this->startedAt ? Time\RFCDecorator::toHttpString($this->startedAt) : null,
			'finishedAt' => $this->finishedAt ? Time\RFCDecorator::toHttpString($this->finishedAt) : null,
			'user' => $this->getUser()->getName(),
			'projectId' => $this->getProject()->getId(),
			'isConnectedToSuspects' => $this->isConnectedToSuspects,
		];
	}

}
