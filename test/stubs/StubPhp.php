<?php

namespace Avorg;

class StubPhp extends Php
{
	use Stub;

	public function array_rand(...$arguments)
	{
		return $this->handleCall(__FUNCTION__, func_get_args());
	}
}