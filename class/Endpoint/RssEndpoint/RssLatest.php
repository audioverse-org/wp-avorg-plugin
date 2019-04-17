<?php

namespace Avorg\Endpoint\RssEndpoint;


use Avorg\Endpoint\RssEndpoint;
use Avorg\Factory;
use Avorg\Php;
use Avorg\PresentationRepository;
use Avorg\Renderer;
use Avorg\RouteFactory;

if (!\defined('ABSPATH')) exit;

class RssLatest extends RssEndpoint
{
	/** @var PresentationRepository $presentationRepository */
	private $presentationRepository;

	protected $routeFormat = "{ language }/podcasts/latest";

	public function __construct(
		Factory $factory,
		Php $php,
		PresentationRepository $presentationRepository,
		Renderer $renderer,
		RouteFactory $routeFactory
	)
	{
		parent::__construct($factory, $php, $renderer, $routeFactory);

		$this->presentationRepository = $presentationRepository;
	}

	protected function getRecordings()
	{
		return $this->presentationRepository->getPresentations();
	}

	protected function getTitle()
	{
		return "AudioVerse Latest Recordings";
	}

	protected function getSubtitle()
	{
		return "The latest recordings at AudioVerse";
	}
}